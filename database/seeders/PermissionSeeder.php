<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Permessi per i clienti
            ['name' => 'view-customers', 'display_name' => 'Visualizza Clienti', 'group' => 'customers'],
            ['name' => 'create-customers', 'display_name' => 'Crea Clienti', 'group' => 'customers'],
            ['name' => 'update-customers', 'display_name' => 'Modifica Clienti', 'group' => 'customers'],
            ['name' => 'delete-customers', 'display_name' => 'Elimina Clienti', 'group' => 'customers'],

            // Permessi per le fatture
            ['name' => 'view-invoices', 'display_name' => 'Visualizza Fatture', 'group' => 'invoices'],
            ['name' => 'create-invoices', 'display_name' => 'Crea Fatture', 'group' => 'invoices'],
            ['name' => 'update-invoices', 'display_name' => 'Modifica Fatture', 'group' => 'invoices'],
            ['name' => 'delete-invoices', 'display_name' => 'Elimina Fatture', 'group' => 'invoices'],

            // Permessi per i ruoli e utenti (solo per admin)
            ['name' => 'manage-roles', 'display_name' => 'Gestione Ruoli', 'group' => 'administration'],
            ['name' => 'manage-users', 'display_name' => 'Gestione Utenti', 'group' => 'administration'],

            // Altri permessi specifici
            ['name' => 'view-dashboard', 'display_name' => 'Visualizza Dashboard', 'group' => 'general'],
            ['name' => 'export-data', 'display_name' => 'Esporta Dati', 'group' => 'general'],
            ['name' => 'import-data', 'display_name' => 'Importa Dati', 'group' => 'general'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
} 