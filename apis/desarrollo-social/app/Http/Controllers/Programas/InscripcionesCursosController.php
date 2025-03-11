<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\beneficiarios_cursos;
use App\Models\adm_gds\bitacora;
use Illuminate\Http\Request;

class InscripcionesCursosController extends Controller
{
    
    public function update (Request $request, beneficiarios_cursos $inscripcion) {

        $request->validate([
            'beneficiario_id' => 'required',
            'detalle_curso_id' => 'required',
            'estado' => 'nullable'
        ]);

        try {

            $inscripcion->beneficiario_id = $request->beneficiario_id;
            $inscripcion->detalle_curso_id = $request->detalle_curso_id;
            $inscripcion->estado = $request->estado;
            $inscripcion->save();
            
            bitacora::create([
                'accion' => $request->estado == 'A' ? bitacora::$acciones[5] : bitacora::$acciones[6] ,
                'tabla' => 'BENEFICIARIOS_CURSOS',
                'descripcion' => 'SE CAMBIO DE ESTADO INSCRIPCION ID : '.$inscripcion->id,
                'created_at' => now(),
                'usuario_id' => auth()->user()->id,
                'beneficiario_id' => $inscripcion->beneficiario_id,
            ]);

            return response('Inscripción actualizada correctamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function destroy (beneficiarios_cursos $inscripcion) {

        try {
            
            bitacora::create([
                'accion' => bitacora::$acciones[7],
                'tabla' => 'BENEFICIARIOS_CURSOS',
                'descripcion' => 'SE ELIMINO REGISTRO INSCRIPCION ID : '.$inscripcion->id .' DETALLE CURSO ID :'.$inscripcion->detalle_curso_id,
                'created_at' => now(),
                'usuario_id' => auth()->user()->id,
                'beneficiario_id' => $inscripcion->beneficiario_id,
            ]);

            $inscripcion->delete();
            
            

            return response('Inscripción eliminada correctamente');
            
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store_beneficiarios(Request $request) {
        $request->validate([
            'beneficiarios' => 'required|array'
        ]);

        try {

            $count_beneficiarios = 0;
            
            foreach ($request->beneficiarios as $beneficiario) {

                if(!isset($beneficiario['id'])) {
                    $inscripcion_curso = beneficiarios_cursos::create([
                        'beneficiario_id' => $beneficiario['beneficiario_id'],
                        'detalle_curso_id' => $beneficiario['detalle_curso_id'],
                        'created_at' => now(),
                        'estado' => 'A'
                    ]);

                    bitacora::create([
                        'accion' => bitacora::$acciones[4],
                        'tabla' => 'BENEFICIARIOS_CURSOS',
                        'descripcion' => 'SE INSCRIBIO BENEFICIARIO ID : '.$inscripcion_curso->id,
                        'created_at' => now(),
                        'usuario_id' => auth()->user()->id,
                        'beneficiario_id' => $beneficiario['beneficiario_id'],
                    ]);

                    $count_beneficiarios ++;
                }
            }
    
            return response($count_beneficiarios.' Beneficiarios nuevos asignados correctamente');
            
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function get_beneficiarios (int $detalle_curso_id, string $year) {

        try {

            $beneficiarios_inscritos = beneficiarios_cursos::with([
                    'beneficiario',
                    'curso.horario',
                    'curso.programa',
                    'curso.curso',
                    'curso.sede',
                ])->where('detalle_curso_id',$detalle_curso_id)
                ->latest('id')
                ->whereYear('created_at',$year)
                ->get();

            return response($beneficiarios_inscritos);
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
