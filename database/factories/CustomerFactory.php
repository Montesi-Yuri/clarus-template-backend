<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition()
    {
        return [
            'tenant_id' => 1,
            'company_name' => $this->faker->company,
            'vat_number' => $this->faker->numerify('##########'),
            'email' => $this->faker->companyEmail,
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'postal_code' => $this->faker->postcode,
            'country' => $this->faker->country,
            'phone' => $this->faker->phoneNumber,
            'notes' => $this->faker->text
        ];
    }
} 