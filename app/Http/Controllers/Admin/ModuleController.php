<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;

class ModuleController extends Controller
{
    public function index()
    {
        return response()->json(Module::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string',
            'route' => 'required|string',
        ]);

        $module = Module::create($validated);
        return response()->json($module, 201);
    }

    public function show(Module $module)
    {
        return response()->json($module);
    }

    public function update(Request $request, Module $module)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'string',
            'type' => 'string',
            'route' => 'string',
        ]);

        $module->update($validated);
        return response()->json($module);
    }

    public function destroy(Module $module)
    {
        $module->delete();
        return response()->json(null, 204);
    }

    public function getActiveModules()
    {
        // Recupera i moduli attivi per l'utente corrente
        // Per ora restituiamo tutti i moduli, in seguito implementeremo la logica dei permessi
        return response()->json(Module::all());
    }

    public function assignModule(Request $request, $tenantId)
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
        ]);

        // Implementa la logica per assegnare il modulo al tenant
        return response()->json(['message' => 'Module assigned successfully']);
    }

    public function updateModule(Request $request, $tenantId, $moduleId)
    {
        // Implementa la logica per aggiornare le impostazioni del modulo per il tenant
        return response()->json(['message' => 'Module settings updated']);
    }

    public function removeModule($tenantId, $moduleId)
    {
        // Implementa la logica per rimuovere il modulo dal tenant
        return response()->json(['message' => 'Module removed successfully']);
    }
} 