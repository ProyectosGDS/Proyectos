<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\cursos_modulos;
use App\Models\adm_gds\detalles_cursos;
use App\Models\adm_gds\modulos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModulosController extends Controller
{
    public function index () {
        try {

            $perfil = strtolower(auth()->user()->perfil->nombre) == 'sysadmin' ? true : false;

            if ($perfil) {
                $modulos = modulos::with(['programa'])
                    ->latest('id')
                    ->get();
                return response($modulos);
            }

            $modulos = modulos::whereHas('programa',function($query){
                    $query->where('programa_id',auth()->user()->programa_id);
                })
                ->with(['programa'])
                ->latest('id')
                ->get();
            return response($modulos);  

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store (Request $request) {
        $request->validate([
            'nombre' => 'required|string|max:80',
            'descripcion' => 'nullable|string|max:255',
            'programa_id' => 'required',
            'fecha_inicial' => 'nullable|required_with:fecha_final|date|date_format:Y-m-d',
            'fecha_final' => 'nullable|required_with:fecha_inicial|date|date_format:Y-m-d|after:fecha_inicial'
        ]);

        try {

            $modulo = modulos::create([
                'nombre' => mb_strtoupper($request->nombre),
                'descripcion' => $request->descripcion ?? null,
                'programa_id' => $request->programa_id,
                'estado' => 'A',
                'fecha_inicial' => $request->fecha_inicial ?? null,
                'fecha_final' => $request->fecha_final ?? null,
                'publico' => 'S',
            ]);

            return response('Módulo creado correctamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function show (modulos $modulo) {
        try {
            return response($modulo->load('programa'));  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function update (Request $request, modulos $modulo) {
        $request->validate([
            'nombre' => 'required|string|max:80',
            'descripcion' => 'nullable|string|max:255',
            'programa_id' => 'required',
            'fecha_inicial' => 'nullable|required_with:fecha_final|date|date_format:Y-m-d',
            'fecha_final' => 'nullable|required_with:fecha_inicial|date|date_format:Y-m-d|after:fecha_inicial'
        ]);

        try {

            $modulo->nombre = mb_strtoupper($request->nombre);
            $modulo->descripcion = $request->descripcion ?? null;
            $modulo->programa_id = $request->programa_id;
            $modulo->estado = $request->estado;
            $modulo->fecha_inicial = $request->fecha_inicial ?? null;
            $modulo->fecha_final = $request->fecha_final ?? null;
            $modulo->publico = $request->publico;
            $modulo->save();

            return response('Módulo modificado correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function destroy (modulos $modulo) {
        try {
            $modulo->estado = 'I';
            $modulo->save();
            
            return response('Módulo desactivado correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function get_cursos (int $modulo_id) {
        try {

            $cursos_modulo = cursos_modulos::with([
                    'modulo',
                    'curso.programa',
                    'curso.curso',
                    'curso.instructor',
                    'curso.sede',
                    'curso.horario',
                    'curso.temporalidad',
                ])
                ->where('modulo_id',$modulo_id)
                ->get();

            return response($cursos_modulo);  

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store_cursos(Request $request) {

        $request->validate([
            'cursos' => 'required|array'
        ]);

        DB::connection('gds')->beginTransaction();

        try {

            foreach ($request->cursos as $curso) {

                if(!isset($curso['detalle_curso_id']) && !isset($curso['modulo_id'])) {

                    $nuevo_curso = detalles_cursos::firstOrCreate(
                        [
                            'programa_id'       => $curso['curso']['programa_id'],
                            'curso_id'          => $curso['curso']['curso']['id'],
                            'instructor_id'     => $curso['curso']['instructor']['id'],
                            'sede_id'           => $curso['curso']['sede']['id'],
                            'horario_id'        => $curso['curso']['horario']['id'],
                            'temporalidad_id'   => $curso['curso']['temporalidad'],
                            'seccion'           => $curso['curso']['seccion'],
                            'capacidad'         => $curso['curso']['capacidad'],
                            'modalidad'         => $curso['curso']['modalidad'],
                        ],
                        [
                            'fecha_inicial' => null,
                            'fecha_final' => null,
                            'publico' => null,
                            'estado' => 'A',
                        ]
                    );

                    if($nuevo_curso) {
                        cursos_modulos::create([
                            'modulo_id' => $curso['modulo']['id'],
                            'detalle_curso_id' => $nuevo_curso->id,
                        ]);
                    } else {
                        DB::connection('gds')->rollBack();
                        return response([
                            'message' => 'Error al crear el detalle del curso',
                        ],422);
                    }
                }
            }

            DB::connection('gds')->commit();
            return response('Cursos asignados correctamente');

        } catch (\Throwable $th) {
            DB::connection('gds')->rollBack();
            return response($th->getMessage(),422);
        }
    }

    public function assign_requirements(Request $request, modulos $modulo) {
        try {

            $modulo->requisitos()->sync($request->requisitos);
            return response('Requisitos asignados correctamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
    
}
