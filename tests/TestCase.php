<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Tenant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function authenticateUser(?Tenant $tenant = null)
    {
        // Se non viene fornito un tenant, ne crea uno nuovo
        if (!$tenant) {
            $tenant = Tenant::factory()->create();
        }

        // Crea e autentica un utente associato al tenant usando Sanctum
        $user = User::factory()->create([
            'tenant_id' => $tenant->id
        ]);

        // Registra il tenant nel container
        $this->app->instance('customer.tenant', $tenant);

        Sanctum::actingAs($user);

        return $user;
    }
}
