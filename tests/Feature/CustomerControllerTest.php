<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Customer;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $tenant;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Crea un tenant e un utente per i test
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id
        ]);
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function it_can_list_customers_for_tenant()
    {
        // Crea alcuni clienti per questo tenant
        Customer::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id
        ]);
        
        // Crea clienti per un altro tenant (non dovrebbero essere visibili)
        Customer::factory()->count(2)->create([
            'tenant_id' => Tenant::factory()->create()->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/customers');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.data');
    }

    /** @test */
    public function it_can_filter_customers()
    {
        // Crea clienti con dati specifici per il test dei filtri
        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'company_name' => 'Test Company',
            'city' => 'Milano',
            'country' => 'IT'
        ]);

        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'company_name' => 'Another Company',
            'city' => 'Roma',
            'country' => 'IT'
        ]);

        // Test filtro per città
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/customers?city=Milano');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.city', 'Milano');

        // Test ricerca per nome azienda
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/customers?search=Test');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.company_name', 'Test Company');
    }

    /** @test */
    public function it_can_soft_delete_and_restore_customer()
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id
        ]);

        // Test soft delete
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/customers/{$customer->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);

        // Test che il cliente non appaia più nella lista
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/customers');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data.data');

        // Test ripristino
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/customers/{$customer->id}/restore");

        $response->assertStatus(200);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function it_cannot_access_customers_from_different_tenant()
    {
        // Crea un cliente per un altro tenant
        $otherTenant = Tenant::factory()->create();
        $customer = Customer::factory()->create([
            'tenant_id' => $otherTenant->id
        ]);

        // Prova ad accedere al cliente
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/customers/{$customer->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Non sei autorizzato ad accedere a questo cliente'
            ]);
    }

    /** @test */
    public function it_returns_correct_filter_options()
    {
        // Crea clienti con diverse città e paesi
        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'city' => 'Milano',
            'country' => 'IT'
        ]);

        Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'city' => 'Roma',
            'country' => 'IT'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/customers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'filters' => [
                        'cities',
                        'countries'
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.filters.cities')
            ->assertJsonCount(1, 'data.filters.countries');
    }

    public function test_can_create_customer()
    {
        $customerData = Customer::factory()->make([
            'tenant_id' => $this->tenant->id
        ])->toArray();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/customers', $customerData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'company_name',
                    'vat_number'
                ]
            ]);
    }

    public function test_can_show_customer()
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/customers/{$customer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $customer->id,
                    'company_name' => $customer->company_name
                ]
            ]);
    }

    public function test_cannot_show_customer_from_different_tenant()
    {
        $otherTenant = Tenant::factory()->create();
        $customer = Customer::factory()->create([
            'tenant_id' => $otherTenant->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/customers/{$customer->id}");

        $response->assertStatus(403);
    }

    public function test_can_update_customer()
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id
        ]);

        $updateData = [
            'company_name' => 'Updated Company Name',
            'vat_number' => 'IT12345678901',
            'email' => 'updated@example.com',
            'address' => 'Updated Address',
            'city' => 'Updated City',
            'postal_code' => '12345',
            'country' => 'Updated Country',
            'phone' => '1234567890',
            'notes' => 'Updated notes'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/customers/{$customer->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'company_name' => 'Updated Company Name'
                ]
            ]);
    }

    public function test_can_delete_customer()
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id
        ]);

        $customerId = $customer->id;
        
        $this->assertDatabaseHas('customers', ['id' => $customerId]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/customers/{$customer->id}");

        $response->assertStatus(204);
        
        $this->assertSoftDeleted('customers', ['id' => $customerId]);
    }

    /** @test */
    public function it_cannot_create_customer_with_duplicate_vat_number()
    {
        // Crea un cliente esistente
        $existingCustomer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'company_name' => 'Existing Company',
            'vat_number' => 'IT12345678901'
        ]);

        // Prova a creare un nuovo cliente con la stessa partita IVA
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/customers', [
            'company_name' => 'New Company',
            'vat_number' => 'IT12345678901',
            'email' => 'test@example.com',
            // ... altri campi obbligatori ...
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['vat_number']
            ])
            ->assertJsonFragment([
                'vat_number' => [
                    "La partita IVA IT12345678901 è già registrata per il cliente Existing Company. " .
                    "Per procedere, inserisci una partita IVA diversa."
                ]
            ]);
    }

    /** @test */
    public function it_cannot_update_customer_with_duplicate_vat_number()
    {
        // Crea due clienti
        $customer1 = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'company_name' => 'First Company',
            'vat_number' => 'IT11111111111'
        ]);

        $customer2 = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'company_name' => 'Second Company',
            'vat_number' => 'IT22222222222'
        ]);

        // Prova ad aggiornare il secondo cliente con la partita IVA del primo
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/customers/{$customer2->id}", [
            'company_name' => 'Second Company',
            'vat_number' => 'IT11111111111',
            'email' => 'test@example.com',
            // ... altri campi obbligatori ...
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['vat_number']
            ])
            ->assertJsonFragment([
                'vat_number' => [
                    "La partita IVA IT11111111111 è già registrata per il cliente First Company. " .
                    "Per procedere, inserisci una partita IVA diversa."
                ]
            ]);
    }

    /** @test */
    public function it_cannot_restore_customer_with_duplicate_vat_number()
    {
        // Crea un cliente attivo
        $activeCustomer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'company_name' => 'Active Company',
            'vat_number' => 'IT12345678901'
        ]);

        // Crea e soft-delete un cliente con la stessa partita IVA ma con un tenant diverso
        $deletedCustomer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'company_name' => 'Deleted Company',
            'vat_number' => 'IT12345678901'
        ]);
        $deletedCustomer->delete();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/customers/{$deletedCustomer->id}/restore");

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['vat_number']
            ])
            ->assertJsonPath('errors.vat_number.0', 
                "Impossibile ripristinare il cliente Deleted Company poiché la partita IVA IT12345678901 " .
                "è già in uso dal cliente Active Company. " .
                "Per procedere con il ripristino, è necessario prima modificare la partita IVA di uno dei due clienti."
            );
    }

    /** @test */
    public function it_can_update_customer_with_same_vat_number()
    {
        // Crea un cliente
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'company_name' => 'Test Company',
            'vat_number' => 'IT12345678901'
        ]);

        // Aggiorna lo stesso cliente mantenendo la stessa partita IVA
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/customers/{$customer->id}", [
            'company_name' => 'Updated Company Name',
            'vat_number' => 'IT12345678901',
            'email' => 'test@example.com',
            // ... altri campi obbligatori ...
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.company_name', 'Updated Company Name')
            ->assertJsonPath('data.vat_number', 'IT12345678901');
    }
} 