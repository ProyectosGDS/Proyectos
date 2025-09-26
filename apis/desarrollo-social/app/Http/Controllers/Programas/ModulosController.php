<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\cursos_modulos;
use App\Models\adm_gds\detalles_cursos;
use App\Models\adm_gds\modulos;
use App\Models\adm_gds\tarifas_cursos;
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
            'descripcion' => 'required|string|max:1000',
            'programa_id' => 'required',
            'seccion' => 'nullable|string|max:45',
            'sede_id' => 'required',
            'modalidad' => 'required|string|max:25',
            'temporalidad_id' => 'required',
            'capacidad' => 'required',
            'paga' => 'required|in:S,N',
            'fecha_inicial' => 'required|required_with:fecha_final|date|date_format:Y-m-d',
            'fecha_final' => 'required|required_with:fecha_inicial|date|date_format:Y-m-d|after:fecha_inicial',
            'publico' => 'required',
            'tarifas.tarifa_menor' => 'required_if:paga,S|decimal:2',
            'tarifas.tarifa_mayor' => 'required_if:paga,S|decimal:2',
            'tarifas.temporalidad' => 'required_if:paga,S|string|max:50',
            'tarifas.no_cuotas' => 'required_if:paga,S|integer|min:1|max:12',
            'tarifas.mes_inicial' => 'required_if:paga,S|required_with:mes_final|date|date_format:Y-m',
            'tarifas.mes_final' => 'required_if:paga,S|required_with:mes_inicial|date|date_format:Y-m|after:mes_inicial',
        ]);

        try {

            $modulo = modulos::create([
                'nombre' => mb_strtoupper($request->nombre),
                'descripcion' => $request->descripcion ?? null,
                'programa_id' => $request->programa_id,
                'seccion' => $request->seccion ?? null,
                'sede_id' => $request->sede_id,
                'modalidad' => $request->modalidad,
                'temporalidad_id' => $request->temporalidad_id,
                'estado' => 'A',
                'fecha_inicial' => $request->fecha_inicial ?? null,
                'fecha_final' => $request->fecha_final ?? null,
                'capacidad' => $request->capacidad,
                'publico' => $request->publico ?? 'S',
                'paga' => $request->paga ?? 'N',
            ]);

            if($request->paga == 'S') {
                if(isset($modulo->id)) {
                    tarifas_cursos::create([
                        'tipo' => 'MODULO',
                        'curso_modulo_id' => $modulo->id,
                        'inscripcion' =>  $request->tarifas['inscripcion'] ?? null,
                        'tarifa_menor' => $request->tarifas['tarifa_menor'],
                        'tarifa_mayor' => $request->tarifas['tarifa_mayor'],
                        'temporalidad' => $request->tarifas['temporalidad'],
                        'no_cuotas' => $request->tarifas['no_cuotas'],
                        'mes_inicial' => $request->tarifas['mes_inicial'],
                        'mes_final' => $request->tarifas['mes_final'],
                    ]);
                }
            }

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
            'descripcion' => 'required|string|max:1000',
            'programa_id' => 'required',
            'seccion' => 'nullable|string|max:45',
            'sede_id' => 'required',
            'modalidad' => 'required|string|max:25',
            'temporalidad_id' => 'required',
            'capacidad' => 'required|int',
            'fecha_inicial' => 'required|required_with:fecha_final|date|date_format:Y-m-d',
            'fecha_final' => 'required|required_with:fecha_inicial|date|date_format:Y-m-d|after:fecha_inicial',
            'paga' => 'required|in:S,N',
            'publico' => 'required|in:S,N',
            'tarifas.tarifa_menor' => 'required_if:paga,S|decimal:2',
            'tarifas.tarifa_mayor' => 'required_if:paga,S|decimal:2',
            'tarifas.temporalidad' => 'required_if:paga,S|string|max:50',
            'tarifas.no_cuotas' => 'required_if:paga,S|integer|min:1|max:12',
            'tarifas.mes_inicial' => 'required_if:paga,S|date|date_format:Y-m',
            'tarifas.mes_final' => 'required_if:paga,S|date|date_format:Y-m|after_or_equal:mes_inicial',
        ]);

        try {
            $modulo->nombre = mb_strtoupper($request->nombre);
            $modulo->descripcion = $request->descripcion;
            $modulo->programa_id = $request->programa_id;
            $modulo->sede_id = $request->sede_id;
            $modulo->seccion = $request->seccion ?? null;
            $modulo->temporalidad_id = $request->temporalidad_id;
            $modulo->modalidad = $request->modalidad;
            $modulo->estado = $request->estado;
            $modulo->fecha_inicial = $request->fecha_inicial;
            $modulo->fecha_final = $request->fecha_final;
            $modulo->publico = $request->publico ?? 'N';
            $modulo->capacidad = $request->capacidad;
            $modulo->paga = $request->paga;
            $modulo->save();

            $modulo->tarifas->inscripcion = $request->tarifas['inscripcion'];
            $modulo->tarifas->tarifa_menor = $request->tarifas['tarifa_menor'];
            $modulo->tarifas->tarifa_mayor = $request->tarifas['tarifa_mayor'];
            $modulo->tarifas->temporalidad = $request->tarifas['temporalidad'];
            $modulo->tarifas->no_cuotas = $request->tarifas['no_cuotas'];
            $modulo->tarifas->mes_inicial = $request->tarifas['mes_inicial'];
            $modulo->tarifas->mes_final = $request->tarifas['mes_final'];
            $modulo->tarifas->save();


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
                    'curso.horarios',
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
                            'temporalidad_id'   => $curso['curso']['temporalidad']['id'],
                        ],
                        [
                            'estado' => 'A',
                        ]
                    );

                    if(isset($nuevo_curso->id)) {
                        cursos_modulos::create([
                            'modulo_id' => $curso['modulo']['id'],
                            'detalle_curso_id' => $nuevo_curso->id,
                        ]);

                        $nuevo_curso->horarios()->sync(collect($curso['curso']['horarios'])->pluck('id'));

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
