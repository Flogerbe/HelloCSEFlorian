<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Administrateur par défaut
        User::factory()->create([
            'name'     => 'Admin HelloCSE',
            'email'    => 'admin@hellocse.fr',
            'password' => Hash::make('password'),
        ]);

        // 5 profils actifs
        Profile::factory()->actif()->count(5)->create();

        // 3 profils en attente
        Profile::factory()->enAttente()->count(3)->create();

        // 2 profils inactifs
        Profile::factory()->inactif()->count(2)->create();
    }
}
