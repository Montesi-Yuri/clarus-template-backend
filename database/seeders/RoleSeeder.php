<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // Ruolo Manager (accesso completo tranne gestione ruoli)
            $managerRole = Role::firstOrCreate(
                [
                    'name' => 'manager',
                    'tenant_id' => $tenant->id,
                    'guard_name' => 'web'
                ],
                [
                    'display_name' => 'Manager',
                    'description' => 'Ruolo con accesso completo alle funzionalità',
                    'is_default' => true
                ]
            );

            // Assegna tutti i permessi tranne quelli amministrativi
            $managerRole->givePermissionTo(
                Permission::whereNotIn('name', ['manage-roles', 'manage-users'])->get()
            );

            // Ruolo Operatore (accesso base)
            $operatorRole = Role::firstOrCreate(
                [
                    'name' => 'operator',
                    'tenant_id' => $tenant->id,
                    'guard_name' => 'web'
                ],
                [
                    'display_name' => 'Operatore',
                    'description' => 'Ruolo con accesso base alle funzionalità',
                    'is_default' => false
                ]
            );

            // Assegna permessi base all'operatore
            $operatorRole->givePermissionTo([
                'view-customers',
                'view-invoices',
                'create-invoices',
                'view-dashboard'
            ]);

            // Ruolo Visualizzatore (solo lettura)
            $viewerRole = Role::firstOrCreate(
                [
                    'name' => 'viewer',
                    'tenant_id' => $tenant->id,
                    'guard_name' => 'web'
                ],
                [
                    'display_name' => 'Visualizzatore',
                    'description' => 'Ruolo con accesso in sola lettura',
                    'is_default' => false
                ]
            );

            // Assegna permessi di sola lettura
            $viewerRole->givePermissionTo([
                'view-customers',
                'view-invoices',
                'view-dashboard'
            ]);
        }
    }
} 