<?php

namespace Tests\Unit;

use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileRequestTest extends TestCase
{
    // -------------------------------------------------------
    // StoreProfileRequest — champs requis
    // -------------------------------------------------------

    #[Test]
    public function store_request_requires_nom(): void
    {
        $validator = $this->makeStoreValidator(['nom' => '']);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nom', $validator->errors()->toArray());
    }

    #[Test]
    public function store_request_requires_prenom(): void
    {
        $validator = $this->makeStoreValidator(['prenom' => '']);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('prenom', $validator->errors()->toArray());
    }

    #[Test]
    public function store_request_passes_with_valid_data(): void
    {
        $validator = $this->makeStoreValidator([
            'nom'    => 'Dupont',
            'prenom' => 'Jean',
            'statut' => 'actif',
        ]);

        $this->assertFalse($validator->fails());
    }

    // -------------------------------------------------------
    // StoreProfileRequest — statut enum
    // -------------------------------------------------------

    #[Test]
    #[DataProvider('validStatuts')]
    public function store_request_accepts_valid_statut(string $statut): void
    {
        $validator = $this->makeStoreValidator([
            'nom'    => 'Test',
            'prenom' => 'User',
            'statut' => $statut,
        ]);

        $this->assertFalse($validator->fails(), "Statut '{$statut}' devrait être valide.");
    }

    #[Test]
    public function store_request_rejects_invalid_statut(): void
    {
        $validator = $this->makeStoreValidator([
            'nom'    => 'Test',
            'prenom' => 'User',
            'statut' => 'invalide',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('statut', $validator->errors()->toArray());
    }

    // -------------------------------------------------------
    // StoreProfileRequest — nom/prenom max:100
    // -------------------------------------------------------

    #[Test]
    public function store_request_rejects_nom_exceeding_100_chars(): void
    {
        $validator = $this->makeStoreValidator([
            'nom'    => str_repeat('a', 101),
            'prenom' => 'Jean',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nom', $validator->errors()->toArray());
    }

    // -------------------------------------------------------
    // UpdateProfileRequest — tous les champs sont optionnels
    // -------------------------------------------------------

    #[Test]
    public function update_request_passes_with_empty_payload(): void
    {
        $validator = $this->makeUpdateValidator([]);

        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function update_request_rejects_invalid_statut(): void
    {
        $validator = $this->makeUpdateValidator(['statut' => 'zombie']);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('statut', $validator->errors()->toArray());
    }

    #[Test]
    public function update_request_accepts_partial_update(): void
    {
        $validator = $this->makeUpdateValidator(['nom' => 'Martin']);

        $this->assertFalse($validator->fails());
    }

    // -------------------------------------------------------
    // Data providers
    // -------------------------------------------------------

    public static function validStatuts(): array
    {
        return [
            'inactif'    => ['inactif'],
            'en_attente' => ['en_attente'],
            'actif'      => ['actif'],
        ];
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    private function makeStoreValidator(array $data): \Illuminate\Validation\Validator
    {
        return Validator::make($data, (new StoreProfileRequest())->rules());
    }

    private function makeUpdateValidator(array $data): \Illuminate\Validation\Validator
    {
        return Validator::make($data, (new UpdateProfileRequest())->rules());
    }
}
