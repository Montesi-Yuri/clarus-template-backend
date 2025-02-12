<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
                $user = Auth::user();
                
                // Aggiungiamo le notifiche non lette
                $unreadNotifications = [
                    // Esempio di struttura notifiche
                    // In seguito questo verrà sostituito con le notifiche reali dal database
                    [
                        'id' => 1,
                        'message' => 'Nuovo documento caricato',
                        'type' => 'info',
                        'read' => false,
                        'created_at' => now()
                    ]
                ];

                return response()->json([
                    'user' => $user,
                    'notifications' => $unreadNotifications,
                    'message' => 'Login successful'
                ]);
            }

            // Credenziali non valide
            return response()->json([
                'message' => 'Credenziali non valide. Controlla email e password e riprova.',
                'type' => 'auth_error',
                'status' => 'error'
            ], 401);

        } catch (ValidationException $e) {
            // Errore di validazione
            return response()->json([
                'message' => 'Dati di login non validi',
                'errors' => $e->errors(),
                'type' => 'validation_error',
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            // Altri errori (server)
            if (config('app.debug')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace(),
                    'status' => 'error'
                ], 500);
            }
            
            return response()->json([
                'message' => 'Si è verificato un errore durante il login',
                'type' => 'server_error',
                'status' => 'error'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
