<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Définit les attributs par défaut du modèle.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_name' => fake()->name(), // Utilisez le nom de colonne correct
            'user_email' => fake()->unique()->safeEmail(), // Utilisez le nom de colonne correct
            'user_password' => bcrypt('password'), // Utilisez le nom de colonne correct
            'user_phone' => fake()->phoneNumber(), // Si cette colonne existe
            'first_login' => true, // Si cette colonne existe
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}