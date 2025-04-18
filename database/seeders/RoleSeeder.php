<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use App\Models\User;



class RoleSeeder extends Seeder
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
        Role::truncate(); // Vide la table avant d'insérer de nouvelles données

        // Réactiver les contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Insère les trois types d'utilisateurs
        Role::create(['label_role' => 'admin']);
        Role::create(['label_role' => 'organizer']);
        Role::create(['label_role' => 'customer']);
    }
}
