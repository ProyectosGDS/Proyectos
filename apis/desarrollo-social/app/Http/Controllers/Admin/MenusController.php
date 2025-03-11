<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\menus;
use Illuminate\Http\Request;

class MenusController extends Controller
{
    public function index() {
        try {
            
            $menus = menus::with(['paginas.padre'])->latest('id')->get();

            return response($menus);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    
    public function store(Request $request) {

        $request->validate([
            'nombre' => 'required|string|max:80',
        ]);

        try {

            $paginas = [];

            $menu = menus::create([
                'nombre' => $request->nombre,
            ]);

            if(count($request->paginas) > 0 ) {
                $paginas = collect($request->paginas)->pluck('id');
            }

            $menu->paginas()->sync($paginas);
            

            return response('menu creado exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
    
    public function update(Request $request, menus $menu) {
        $request->validate([
            'nombre' => 'required|string|max:80',
        ]);

        try {

            $paginas = [];

            $menu->nombre = $request->nombre;
            $menu->save();

            if(count($request->paginas) > 0 ) {
                $paginas = collect($request->paginas)->pluck('id');
            }

            $menu->paginas()->sync($paginas);

            return response('menu actualizado exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    
    public function destroy(menus $menu) {
        try {

            $menu->delete();

            return response('menu eliminado exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
