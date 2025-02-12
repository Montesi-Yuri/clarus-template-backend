<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'name' => 'customers',
                'display_name' => 'Gestione Clienti',
                'description' => 'Gestione anagrafica clienti',
                'icon' => 'users',
                'is_core' => true,
                'enabled' => true,
                'order' => 1
            ],
            [
                'name' => 'invoices',
                'display_name' => 'Fatturazione',
                'description' => 'Gestione fatture e documenti',
                'icon' => 'file-text',
                'is_core' => true,
                'enabled' => true,
                'order' => 2
            ],
            [
                'name' => 'users',
                'display_name' => 'Gestione Utenti',
                'description' => 'Gestione degli utenti del tenant',
                'icon' => 'users',
                'is_core' => true,
                'enabled' => true,
                'order' => 3,
                'requires_permission' => 'manage-users'
            ],
            [
                'name' => 'roles',
                'display_name' => 'Gestione Ruoli',
                'description' => 'Gestione dei ruoli e permessi',
                'icon' => 'shield',
                'is_core' => true,
                'enabled' => true,
                'order' => 4,
                'requires_permission' => 'manage-roles'
            ]
        ];

        foreach ($modules as $module) {
            Module::firstOrCreate(
                ['name' => $module['name']],
                $module
            );
        }
    }
} 