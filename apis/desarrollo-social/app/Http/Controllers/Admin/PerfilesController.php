<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\perfiles;
use Illuminate\Http\Request;

class PerfilesController extends Controller
{
    public function index() {
        try {
            
            $perfiles = perfiles::with([
                'rol',
                'menu'
            ])->latest('id')->get();

            return response($perfiles);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    
    public function store(Request $request) {
        $request->validate([
            'nombre' => 'required|string|max:80',
            'descripcion' => 'required|string|max:255',
            'rol_id' => 'required|integer',
            'menu_id' => 'required|integer',
        ]);

        try {

            $perfil = perfiles::create([
                'nombre' => mb_strtoupper($request->nombre),
                'rol_id' => $request->rol_id,
                'menu_id' => $request->menu_id,
                'descripcion' => $request->descripcion,
            ]);

            return response('Perfil creado exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    

    
    public function update(Request $request, perfiles $perfile) {
        $request->validate([
            'nombre' => 'required|string|max:80',
            'descripcion' => 'required|string|max:255',
            'rol_id' => 'required|integer',
            'menu_id' => 'required|integer',

        ]);

        try {

            $perfile->nombre = mb_strtoupper($request->nombre);
            $perfile->rol_id = $request->rol_id;
            $perfile->menu_id = $request->menu_id;
            $perfile->descripcion = $request->descripcion;
            $perfile->save();
            
            return response('Perfil actualizado exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    
    public function destroy(perfiles $perfile) {
        try {

            $perfile->delete();
            
            return response('Perfil eliminado exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
