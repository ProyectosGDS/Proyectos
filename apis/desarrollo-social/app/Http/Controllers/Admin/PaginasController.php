<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\paginas;
use Illuminate\Http\Request;

class PaginasController extends Controller
{
    public function index() {
        try {
            
            $paginas = paginas::with(['padre'])->latest('id')->get();

            return response($paginas);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    
    public function store(Request $request) {

        $request->validate([
            'titulo' => 'required|string|max:80',
        ]);

        try {

            $pagina = paginas::create([
                'titulo' => $request->titulo,
                'link' => $request->link ?? '#',
                'icon' => $request->icon ?? 'fas fa-circle',
                'orden' => $request->orden ?? null,
                'pagina_id' => $request->pagina_id ?? null,
            ]);

            return response('Página creada exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
    
    public function update(Request $request, paginas $pagina) {
        $request->validate([
            'titulo' => 'required|string|max:80',
        ]);

        try {

            $pagina->titulo = $request->titulo;
            $pagina->link = $request->link ?? '#';
            $pagina->icon = $request->icon ?? 'fas fa-circle';
            $pagina->orden = $request->orden ?? null;
            $pagina->pagina_id = $request->pagina_id ?? null;
            $pagina->save();

            return response('Página actualizada exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    
    public function destroy(paginas $pagina) {
        try {

            $pagina->delete();

            return response('Página eliminada exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
