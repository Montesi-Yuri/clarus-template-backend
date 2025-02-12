<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Tenant;
use Carbon\Carbon;

class ModuleTenantSeeder extends Seeder
{
    public function run(): void
    {
        $modules = Module::all();
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // Assegna i moduli core a tutti i tenant
            $coreModules = $modules->where('is_core', true);
            foreach ($coreModules as $module) {
                $tenant->modules()->attach($module->id, [
                    'is_active' => true,
                    'valid_until' => null, // I moduli core non scadono
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
} 