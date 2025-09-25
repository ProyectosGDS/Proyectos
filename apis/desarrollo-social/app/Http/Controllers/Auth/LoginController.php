<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\usuarios;
use App\Rules\ValidateCui;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    
    public function authenticate(Request $request) {
        // Validación de datos de entrada
        $request->validate([
            'cui' => ['required','numeric','digits:13',new ValidateCui,'exists:usuarios,cui'],
            'password' => 'required|string|min:8'
        ]);

        $user = usuarios::where('cui', $request->cui)->first();

        // Verificar la existencia del usuario y la contraseña
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'cui' => ['Las credenciales proporcionadas son incorrectas.'],
            ])->status(401); // 401 para credenciales incorrectas
        }

        try {
            // Asegurarse de que el 'aud' sea válido
            $aud = $request->header('Origin');
            $receivers = config('jwt.receivers');

            if (!in_array($aud, $receivers)) {
                return response()->json(['message' => 'Origen de la solicitud no permitido.'], 403);
            }
            
            // Generar el payload del JWT
            $payload = [
                'sub' => $user->id,
                // Puedes añadir más información aquí si es necesario
            ];

            $accessToken = $user->createToken($payload, $aud);
            
            // // Cargar datos del usuario y sus relaciones
            $user->load('dependencia');
            // $user->append('permisos', 'menu');

            // Retornar el token y los datos del usuario en una respuesta JSON
            return response()->json([
                'user' => $user->toArray(),
                'access_token' => $accessToken,
            ]);

        } catch (\Throwable $th) {
            // Manejo de errores genéricos en caso de que algo falle internamente
            return response()->json([
                'message' => 'Error interno del servidor. Intente de nuevo más tarde.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function verifyAuth() {
        
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'No se encontró el usuario.'], 404);
        }
        
        // Retorna los datos completos del usuario
        $user->load('dependencia');
        // $user->append('permisos', 'menu', 'perfil');

        return response()->json([
            'user' => $user->toArray()
        ]);
    }

    

    public function logout() {
        Auth::logout();
        $cookie = Cookie::forget(base64_encode('access_token'));
        return response(['message' => 'Sesión cerrada exitosamente'])
                         ->withCookie($cookie);
    }
}
