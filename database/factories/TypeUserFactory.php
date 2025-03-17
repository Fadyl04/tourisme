<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TypeUser>
 */
class TypeUserFactory extends Factory
{
    // Valeurs prédéfinies pour label_type
    protected static $labels = ['admin', 'director', 'customer'];
    // Index pour parcourir les valeurs prédéfinies
    protected static $index = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Récupère la valeur actuelle de label_type
        $label_type = self::$labels[self::$index];
        // Passe à la valeur suivante (ou revient au début)
        self::$index = (self::$index + 1) % count(self::$labels);
        
        return [
            //
            'label_type' => $label_type,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
