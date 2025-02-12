<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Crea il primo tenant di test
        $tenant1 = Tenant::create([
            'company_name' => 'Acme Corporation',
            'vat_number' => 'IT12345678901',
            'address' => 'Via Roma 123',
            'city' => 'Milano',
            'postal_code' => '20100',
            'country' => 'Italy',
        ]);

        // Crea un utente per il primo tenant
        User::create([
            'tenant_id' => $tenant1->id,
            'name' => 'John Doe',
            'email' => 'john@acme.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        // Crea il secondo tenant di test
        $tenant2 = Tenant::create([
            'company_name' => 'Tech Solutions',
            'vat_number' => 'IT98765432101',
            'address' => 'Via Verdi 456',
            'city' => 'Roma',
            'postal_code' => '00100',
            'country' => 'Italy',
        ]);

        // Crea un utente per il secondo tenant
        User::create([
            'tenant_id' => $tenant2->id,
            'name' => 'Jane Smith',
            'email' => 'jane@techsolutions.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);
    }
} 