<?php

namespace Database\Factories;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'nom'    => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),
            'image'  => null,
            'statut' => $this->faker->randomElement(['inactif', 'en_attente', 'actif']),
        ];
    }

    /**
     * État : profil actif.
     */
    public function actif(): static
    {
        return $this->state(['statut' => 'actif']);
    }

    /**
     * État : profil inactif.
     */
    public function inactif(): static
    {
        return $this->state(['statut' => 'inactif']);
    }

    /**
     * État : profil en attente.
     */
    public function enAttente(): static
    {
        return $this->state(['statut' => 'en_attente']);
    }
}
