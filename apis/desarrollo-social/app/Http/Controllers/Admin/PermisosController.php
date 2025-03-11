<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\permisos;
use Illuminate\Http\Request;

class PermisosController extends Controller
{
    public function index() {
        try {
            
            $permisos = permisos::latest('id')->get();

            return response($permisos);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    
    public function store(Request $request) {
        $request->validate([
            'nombre' => 'required|string|max:80',
            'app'   => 'required|string|max:80',
            'descripcion' => 'required|string|max:255',
            'grupo' => 'required|string|max:80',
        ]);

        try {

            $permiso = permisos::create([
                'nombre' => $request->nombre,
                'app'   => mb_strtoupper($request->app),
                'grupo'   => mb_strtoupper($request->grupo),
                'descripcion' => $request->descripcion
            ]);

            return response('Permiso creado exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    

    
    public function update(Request $request, permisos $permiso) {
        $request->validate([
            'nombre' => 'required|string|max:80',
            'app'   => 'required|string|max:80',
            'descripcion' => 'required|string|max:255',
            'grupo' => 'required|string|max:80',
        ]);

        try {

            $permiso->nombre = strtolower($request->nombre);
            $permiso->app = mb_strtoupper($request->app);
            $permiso->grupo = mb_strtoupper($request->grupo);
            $permiso->descripcion = ucfirst($request->descripcion);
            $permiso->save();
            
            return response('Permiso actualizado exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    
    public function destroy(permisos $permiso) {
        try {

            $permiso->delete();
            
            return response('Permiso eliminado exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
