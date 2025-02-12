<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Tenant;
use Faker\Factory;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create();
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            for ($i = 0; $i < 40; $i++) {
                $isItalian = $faker->boolean(70); // 70% probabilità di aziende italiane

                Customer::create([
                    'company_name' => $faker->company(),
                    'vat_number' => $isItalian 
                        ? 'IT' . $faker->numerify('###########') 
                        : $faker->bothify('??###########'),
                    'address' => $faker->streetAddress(),
                    'city' => $faker->city(),
                    'postal_code' => $faker->postcode(),
                    'country' => $isItalian ? 'IT' : $faker->countryCode(),
                    'phone' => $faker->phoneNumber(),
                    'email' => $faker->companyEmail(),
                    'website' => $faker->url(),
                    'notes' => $faker->optional(0.7)->sentence(), // 70% probabilità di avere note
                    'tenant_id' => $tenant->id,
                    'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                    'updated_at' => now()
                ]);
            }
        }
    }
} 