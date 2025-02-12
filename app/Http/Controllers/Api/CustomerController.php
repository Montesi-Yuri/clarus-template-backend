<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\HttpResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\CustomerResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class CustomerController extends Controller
{
    use HttpResponses, AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Customer::class);

        $query = Customer::query()
            ->when(!$request->user()->isAdmin(), function ($query) use ($request) {
                $query->where('tenant_id', $request->user()->tenant_id);
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('company_name', 'like', "%{$search}%")
                        ->orWhere('vat_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->city, function ($query, $city) {
                $query->where('city', 'like', "%{$city}%");
            })
            ->when($request->country, function ($query, $country) {
                $query->where('country', $country);
            })
            ->orderBy('company_name');

        $customers = $query->paginate(10);

        return response()->json([
            'data' => $customers
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'birth_date' => 'nullable|date',
                'gender' => 'nullable|in:M,F,O',
                'is_individual' => 'boolean',
                'is_company' => 'boolean',
                'company_name' => 'required_if:is_company,true|nullable|string|max:255',
                'vat_number' => 'required_if:is_company,true|nullable|string|max:20',
                'fiscal_code' => 'required_if:is_individual,true|nullable|string|max:16',
                'sdi_code' => 'nullable|string|max:7',
                'is_private' => 'boolean',
                'is_public' => 'boolean',
                'email' => 'required|email',
                'address' => 'nullable|string',
                'city' => 'nullable|string',
                'postal_code' => 'nullable|string',
                'country' => 'nullable|string',
                'phone' => 'nullable|string',
                'website' => 'nullable|string|url|max:255',
                'notes' => 'nullable|string'
            ]);

            $customer = Customer::create([
                'tenant_id' => Auth::user()->tenant_id,
                ...$validated
            ]);

            return $this->success($customer, 'Customer created successfully', 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Errore durante la creazione del cliente',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return $this->error('Error creating customer: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            // Cerchiamo il cliente senza applicare il global scope del tenant
            $customer = Customer::withoutGlobalScope('tenant')->findOrFail($id);
            
            // Se il cliente appartiene a un altro tenant, restituiamo 403
            if ($customer->tenant_id !== auth()->user()->tenant_id) {
                return response()->json(['message' => 'Non sei autorizzato ad accedere a questo cliente'], 403);
            }
            
            return new CustomerResource($customer);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Cliente non trovato'], 404);
        }
    }

    public function update(Request $request, Customer $customer)
    {
        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        try {
            \Log::info('Update customer request data:', $request->all());
            
            $validated = $request->validate([
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'birth_date' => 'nullable|date',
                'gender' => 'nullable|in:M,F,O',
                'is_individual' => 'boolean',
                'is_company' => 'boolean',
                'company_name' => 'required_if:is_company,true|nullable|string|max:255',
                'vat_number' => 'required_if:is_company,true|nullable|string|max:20',
                'fiscal_code' => 'required_if:is_individual,true|nullable|string|max:16',
                'sdi_code' => 'nullable|string|max:7',
                'is_private' => 'boolean',
                'is_public' => 'boolean',
                'email' => 'required|email',
                'address' => 'nullable|string',
                'city' => 'nullable|string',
                'postal_code' => 'nullable|string',
                'country' => 'nullable|string',
                'phone' => 'nullable|string',
                'website' => 'nullable|string|url|max:255',
                'notes' => 'nullable|string'
            ]);

            \Log::info('Validated data:', $validated);
            
            $customer->update($validated);
            
            \Log::info('Customer after update:', $customer->toArray());

            return new CustomerResource($customer);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiornamento del cliente',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating customer: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error updating customer: ' . $e->getMessage()
            ], 400);
        }
    }

    public function destroy(Customer $customer)
    {
        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        try {
            \Log::info('Attempting to soft delete customer', ['customer_id' => $customer->id]);
            
            $customer->delete(); // Ora esegue un soft delete
            
            return response()->json(null, 204);
        } catch (\Exception $e) {
            \Log::error('Error soft deleting customer', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Error deleting customer: ' . $e->getMessage()], 400);
        }
    }

    // Opzionale: metodo per il ripristino di un cliente eliminato
    public function restore($id)
    {
        try {
            $customer = Customer::withTrashed()
                ->where('tenant_id', Auth::user()->tenant_id)
                ->findOrFail($id);
            
            try {
                $customer->restore();
                return new CustomerResource($customer);
            } catch (ValidationException $e) {
                return response()->json([
                    'message' => 'Impossibile ripristinare il cliente',
                    'errors' => $e->errors()
                ], 422);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Cliente non trovato'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Errore durante il ripristino del cliente'], 400);
        }
    }

    // Opzionale: metodo per l'eliminazione definitiva
    public function forceDelete($id)
    {
        try {
            $customer = Customer::withTrashed()
                ->where('tenant_id', Auth::user()->tenant_id)
                ->findOrFail($id);
            
            $customer->forceDelete();
            
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Customer not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error force deleting customer'], 400);
        }
    }
} 