<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\UserController;

// Route pubbliche
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Route protette
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Rotta per ottenere i dati dell'utente
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
            'notifications' => [] // Aggiungi qui la logica per le notifiche
        ]);
    })->name('api.user');
    
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Modules routes
    Route::get('/modules', [ModuleController::class, 'index']);
    Route::get('/modules/active', [ModuleController::class, 'getActiveModules']);

    // Customer routes
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::get('/customers/{customer}', [CustomerController::class, 'show']);
    Route::put('/customers/{customer}', [CustomerController::class, 'update']);
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy']);
    Route::post('/customers/{id}/restore', [CustomerController::class, 'restore']);
    Route::delete('/customers/{id}/force', [CustomerController::class, 'forceDelete']);

    // Ruoli e Permessi
    Route::get('permissions', [RoleController::class, 'permissions']);
    Route::get('roles/available-tenants', [RoleController::class, 'getAvailableTenants']);
    Route::apiResource('roles', RoleController::class);
    Route::get('users/{user}/roles', [UserRoleController::class, 'show']);
    Route::put('users/{user}/roles', [UserRoleController::class, 'update']);

    // Gestione Utenti
    Route::middleware([\App\Http\Middleware\CheckIsAdmin::class])->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });
});

// Route admin
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // Gestione Moduli
    Route::get('/modules', [ModuleController::class, 'index']);
    Route::post('/modules', [ModuleController::class, 'store']);
    Route::get('/modules/{module}', [ModuleController::class, 'show']);
    Route::put('/modules/{module}', [ModuleController::class, 'update']);
    Route::delete('/modules/{module}', [ModuleController::class, 'destroy']);

    // Gestione Tenant e loro moduli
    Route::get('/tenants', [TenantController::class, 'index']);
    Route::get('/tenants/{tenant}', [TenantController::class, 'show']);
    Route::post('/tenants/{tenant}/modules', [ModuleController::class, 'assignModule']);
    Route::put('/tenants/{tenant}/modules/{module}', [ModuleController::class, 'updateModule']);
    Route::delete('/tenants/{tenant}/modules/{module}', [ModuleController::class, 'removeModule']);

    // Gestione Admin
    Route::get('/admins', [AdminController::class, 'index']);
    Route::post('/admins', [AdminController::class, 'store']);
    Route::delete('/admins/{user}', [AdminController::class, 'removeAdmin']);
});