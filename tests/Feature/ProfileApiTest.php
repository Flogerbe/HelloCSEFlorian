<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    private const PROFILES_URL       = '/api/profiles';
    private const ADMIN_PROFILES_URL = '/api/admin/profiles';
    private const LOGIN_URL          = '/api/login';
    private const LOGOUT_URL         = '/api/logout';

    private User $admin;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();


        $this->admin = User::factory()->create([
            'email'    => 'test@hellocse.com',
            'password' => bcrypt('password'),
        ]);

        $this->token = $this->admin->createToken('api-token')->plainTextToken;
    }

    // GET /api/profiles (public)

    #[Test]
    public function it_returns_only_active_profiles_publicly(): void
    {
        Profile::factory()->actif()->count(3)->create();
        Profile::factory()->inactif()->count(2)->create();
        Profile::factory()->enAttente()->count(1)->create();

        $response = $this->getJson(self::PROFILES_URL);

        $response->assertOk()
                 ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function public_profile_list_does_not_expose_statut(): void
    {
        Profile::factory()->actif()->create();

        $response = $this->getJson(self::PROFILES_URL);

        $response->assertOk();

        foreach ($response->json('data') as $profile) {
            $this->assertArrayNotHasKey('statut', $profile);
        }
    }

    // POST /api/profiles (auth requis)

    #[Test]
    public function authenticated_admin_can_create_a_profile(): void
    {
        Storage::fake('public');

        $response = $this->withToken($this->token)
                         ->postJson(self::PROFILES_URL, [
                             'nom'    => 'Dupont',
                             'prenom' => 'Jean',
                             'image'  => UploadedFile::fake()->image('photo.jpg'),
                             'statut' => 'actif',
                         ]);

        $response->assertCreated()
                 ->assertJsonPath('data.nom', 'Dupont');

        $this->assertDatabaseHas('profiles', ['nom' => 'Dupont', 'statut' => 'actif']);
    }

    #[Test]
    public function unauthenticated_user_cannot_create_a_profile(): void
    {
        $this->postJson(self::PROFILES_URL, [
            'nom'    => 'Dupont',
            'prenom' => 'Jean',
        ])->assertUnauthorized();
    }

    #[Test]
    public function creating_profile_requires_nom_and_prenom(): void
    {
        $this->withToken($this->token)
             ->postJson(self::PROFILES_URL, [])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['nom', 'prenom']);
    }

    // PUT /api/profiles/{profile} (auth requis)

    #[Test]
    public function authenticated_admin_can_update_a_profile(): void
    {
        $profile = Profile::factory()->actif()->create();

        $response = $this->withToken($this->token)
                         ->putJson("/api/profiles/{$profile->id}", [
                             'nom'    => 'Martin',
                             'statut' => 'inactif',
                         ]);

        $response->assertOk()
                 ->assertJsonPath('data.nom', 'Martin')
                 ->assertJsonPath('data.statut', 'inactif');
    }

    #[Test]
    public function unauthenticated_user_cannot_update_a_profile(): void
    {
        $profile = Profile::factory()->actif()->create();

        $this->putJson("/api/profiles/{$profile->id}", ['nom' => 'Martin'])
             ->assertUnauthorized();
    }

    // DELETE /api/profiles/{profile} (auth requis)

    #[Test]
    public function authenticated_admin_can_delete_a_profile(): void
    {
        $profile = Profile::factory()->actif()->create();

        $this->withToken($this->token)
             ->deleteJson("/api/profiles/{$profile->id}")
             ->assertOk();

        $this->assertDatabaseMissing('profiles', ['id' => $profile->id]);
    }

    #[Test]
    public function unauthenticated_user_cannot_delete_a_profile(): void
    {
        $profile = Profile::factory()->actif()->create();

        $this->deleteJson("/api/profiles/{$profile->id}")
             ->assertUnauthorized();
    }

    // Login / Logout

    #[Test]
    public function admin_can_login_and_receive_a_token(): void
    {
        $this->postJson(self::LOGIN_URL, [
            'email'    => $this->admin->email,
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['token']);
    }

    #[Test]
    public function login_fails_with_invalid_credentials(): void
    {
        $this->postJson(self::LOGIN_URL, [
            'email'    => 'admin@hellocse.fr',
            'password' => 'mauvais',
        ])->assertUnprocessable();
    }

    #[Test]
    public function admin_can_logout(): void
    {
        $this->withToken($this->token)
             ->postJson(self::LOGOUT_URL)
             ->assertOk();
    }


    // GET /api/admin/profiles (auth requis)


    #[Test]
    public function authenticated_admin_can_list_all_profiles_with_statut(): void
    {
        Profile::factory()->actif()->count(2)->create();
        Profile::factory()->inactif()->count(1)->create();
        Profile::factory()->enAttente()->count(1)->create();

        $response = $this->withToken($this->token)
                         ->getJson(self::ADMIN_PROFILES_URL);

        $response->assertOk()
                 ->assertJsonCount(4, 'data');

        // Le champ statut est visible pour les admins
        foreach ($response->json('data') as $profile) {
            $this->assertArrayHasKey('statut', $profile);
        }
    }

    #[Test]
    public function unauthenticated_user_cannot_access_admin_profile_list(): void
    {
        $this->getJson(self::ADMIN_PROFILES_URL)
             ->assertUnauthorized();
    }

    #[Test]
    public function admin_profile_list_includes_all_statuts(): void
    {
        Profile::factory()->actif()->create(['nom' => 'Actif']);
        Profile::factory()->inactif()->create(['nom' => 'Inactif']);
        Profile::factory()->enAttente()->create(['nom' => 'EnAttente']);

        $response = $this->withToken($this->token)
                         ->getJson(self::ADMIN_PROFILES_URL);

        $statuts = collect($response->json('data'))->pluck('statut')->toArray();

        $this->assertContains('actif', $statuts);
        $this->assertContains('inactif', $statuts);
        $this->assertContains('en_attente', $statuts);
    }
}
