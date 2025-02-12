<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ModuleController extends Controller
{
    /**
     * Recupera tutti i moduli disponibili
     */
    public function index(): JsonResponse
    {
        $modules = Module::orderBy('order')->get();
        return response()->json($modules);
    }

    /**
     * Recupera i moduli attivi per il tenant corrente
     */
    public function getActiveModules(): JsonResponse
    {
        $tenant = auth()->user()->tenant;
        
        if (!$tenant) {
            // Se è un admin, restituisce tutti i moduli
            return $this->index();
        }

        $modules = $tenant->modules()
            ->where('is_active', true)
            ->whereNull('valid_until')
            ->orWhere('valid_until', '>', now())
            ->orderBy('order')
            ->get();

        return response()->json($modules);
    }

} 