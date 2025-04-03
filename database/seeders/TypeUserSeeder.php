<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeUser;
use Illuminate\Support\Facades\DB;
use App\Models\User;


class TypeUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Désactiver les contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Supprimer les utilisateurs liés
        /* User::truncate(); */
        TypeUser::truncate(); // Vide la table avant d'insérer de nouvelles données

        // Réactiver les contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Insère les trois types d'utilisateurs
        TypeUser::create(['label_type' => 'admin']);
        TypeUser::create(['label_type' => 'organizer']);
        TypeUser::create(['label_type' => 'customer']);
    }
}
