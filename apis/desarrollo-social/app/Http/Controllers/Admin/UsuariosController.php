<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\usuarios;
use App\Rules\ValidateCui;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuariosController extends Controller
{

    public function index() {
        try {
            $usuarios = usuarios::with([
                    'dependencia',
                    'perfil'
                ])->get();
            return response($usuarios);
        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    public function store(Request $request) {
        $request->validate([
            'cui' => ['required','numeric','digits:13',new ValidateCui,'unique:usuarios,cui'],
            'nombre' => 'required|string|max:150',
            'dependencia_id' => 'required'
        ]);

        try {

            $year = date('Y');
            
            $usuario = usuarios::create([
                'cui' => $request->cui,
                // 'password' => Hash::make('MuniGuateGDS'.$year),
                'password' => Hash::make('muniguate'.$year),
                'nombre' => mb_strtoupper(trim($request->nombre)),
                'dependencia_id' => $request->dependencia_id,
                'perfil_id' => $request->perfil_id ?? null
            ]);



            return response('Usuario creado exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }


    public function show(usuarios $usuario) {
        try {
            return response(
                $usuario->load([
                    'dependencia',
                    'perfil'
                ])
            );

        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }


    public function update(Request $request, usuarios $usuario) {
        
        $request->validate([
            'cui' => ['required','numeric','digits:13', new ValidateCui, Rule::unique('usuarios', 'cui')->ignore($usuario->id)],
            'nombre' => 'required|string|max:150',
            'dependencia_id' => 'required'
        ]);

        try {

            $usuario->cui = $request->cui;
            $usuario->nombre = mb_strtoupper(trim($request->nombre));
            $usuario->dependencia_id = $request->dependencia_id;
            $usuario->perfil_id = $request->perfil_id ?? null;
            $usuario->save();

            return response('Usuario modificado exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    public function destroy(usuarios $usuario) {

        try {

            $usuario->deleted_at = now();
            $usuario->save();

            return response('Usuario eliminado exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    function restartPassword(usuarios $usuario) {
        try {

            $year = date('Y');
            // $usuario->password = Hash::make('MuniGuateGDS'.$year);
            $usuario->password = Hash::make('muniguate'.$year);
            $usuario->save();

            return response('Contraseña reiniciada con exito');

        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

}
