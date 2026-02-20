<?php

namespace Tests\Unit;

use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileModelTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------
    // Attributs & configuration du modèle
    // -------------------------------------------------------

    #[Test]
    public function it_has_the_expected_fillable_attributes(): void
    {
        $expected = ['nom', 'prenom', 'image', 'statut'];

        $this->assertSame($expected, (new Profile())->getFillable());
    }

    #[Test]
    public function statut_defaults_to_en_attente(): void
    {
        $profile = Profile::create([
            'nom'    => 'Dupont',
            'prenom' => 'Jean',
        ]);

        $this->assertSame('en_attente', $profile->statut);
    }

    // -------------------------------------------------------
    // Scope scopeActif
    // -------------------------------------------------------

    #[Test]
    public function scope_actif_returns_only_active_profiles(): void
    {
        Profile::factory()->actif()->count(3)->create();
        Profile::factory()->inactif()->count(2)->create();
        Profile::factory()->enAttente()->count(1)->create();

        $this->assertCount(3, Profile::actif()->get());
    }

    #[Test]
    public function scope_actif_excludes_inactif_profiles(): void
    {
        Profile::factory()->inactif()->create(['nom' => 'Hidden']);

        $this->assertEmpty(Profile::actif()->get());
    }

    #[Test]
    public function scope_actif_excludes_en_attente_profiles(): void
    {
        Profile::factory()->enAttente()->create(['nom' => 'Pending']);

        $this->assertEmpty(Profile::actif()->get());
    }

    // -------------------------------------------------------
    // Factory states
    // -------------------------------------------------------

    #[Test]
    public function factory_actif_state_sets_statut_to_actif(): void
    {
        $profile = Profile::factory()->actif()->make();

        $this->assertSame('actif', $profile->statut);
    }

    #[Test]
    public function factory_inactif_state_sets_statut_to_inactif(): void
    {
        $profile = Profile::factory()->inactif()->make();

        $this->assertSame('inactif', $profile->statut);
    }

    #[Test]
    public function factory_en_attente_state_sets_statut_to_en_attente(): void
    {
        $profile = Profile::factory()->enAttente()->make();

        $this->assertSame('en_attente', $profile->statut);
    }

    // -------------------------------------------------------
    // Persistance
    // -------------------------------------------------------

    #[Test]
    public function it_can_be_created_and_persisted(): void
    {
        Profile::factory()->actif()->create([
            'nom'    => 'Martin',
            'prenom' => 'Sophie',
        ]);

        $this->assertDatabaseHas('profiles', [
            'nom'    => 'Martin',
            'prenom' => 'Sophie',
            'statut' => 'actif',
        ]);
    }

    #[Test]
    public function it_can_be_updated(): void
    {
        $profile = Profile::factory()->actif()->create();

        $profile->update(['statut' => 'inactif']);

        $this->assertSame('inactif', $profile->fresh()->statut);
    }

    #[Test]
    public function it_can_be_deleted(): void
    {
        $profile = Profile::factory()->actif()->create();
        $id      = $profile->id;

        $profile->delete();

        $this->assertDatabaseMissing('profiles', ['id' => $id]);
    }
}
