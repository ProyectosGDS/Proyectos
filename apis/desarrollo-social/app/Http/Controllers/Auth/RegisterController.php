<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\usuarios;
use App\Rules\ValidateCui;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request) {
        $request->validate([
            'cui' => ['required','numeric','digits:13',new ValidateCui,'unique:usuarios,cui'],
            'password' => 'required|string|min:8|max:15|confirmed',
            'nombre' => 'required|string|max:150',
            'id_dependencia' => 'required'
        ]);

        try {
            
            $usuario = usuarios::create([
                'cui' => $request->cui,
                'password' => Hash::make($request->password),
                'nombre' => mb_strtoupper(trim($request->nombre)),
                'id_dependencia' => $request->id_dependencia,
            ]);

            return response('Usuario creado exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
