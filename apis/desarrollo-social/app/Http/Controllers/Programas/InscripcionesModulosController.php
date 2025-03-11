<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\beneficiarios_modulos;
use App\Models\adm_gds\bitacora;
use Illuminate\Http\Request;

class InscripcionesModulosController extends Controller
{
    
    public function update (Request $request, beneficiarios_modulos $inscripcion) {

        $request->validate([
            'beneficiario_id' => 'required',
            'modulo_id' => 'required',
            'estado' => 'nullable'
        ]);

        try {

            $inscripcion->beneficiario_id = $request->beneficiario_id;
            $inscripcion->modulo_id = $request->modulo_id;
            $inscripcion->estado = $request->estado;
            $inscripcion->save();
            
            bitacora::create([
                'accion' => $request->estado == 'A' ? bitacora::$acciones[9] : bitacora::$acciones[10] ,
                'tabla' => 'BENEFICIARIOS_MODULOS',
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

    public function destroy (beneficiarios_modulos $inscripcion) {

        try {
            
            bitacora::create([
                'accion' => bitacora::$acciones[11],
                'tabla' => 'BENEFICIARIOS_MODULOS',
                'descripcion' => 'SE ELIMINO REGISTRO INSCRIPCION ID : '.$inscripcion->id .' MODULO ID :'.$inscripcion->modulo_id,
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
                    $inscripcion_modulo = beneficiarios_modulos::create([
                        'beneficiario_id' => $beneficiario['beneficiario_id'],
                        'modulo_id' => $beneficiario['modulo_id'],
                        'created_at' => now(),
                        'estado' => 'A'
                    ]);

                    bitacora::create([
                        'accion' => bitacora::$acciones[8],
                        'tabla' => 'BENEFICIARIOS_MODULOS',
                        'descripcion' => 'SE INSCRIBIO BENEFICIARIO ID : '.$inscripcion_modulo->id,
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

    public function get_beneficiarios (int $modulo_id, string $year) {

        try {

            $beneficiarios_inscritos = beneficiarios_modulos::with([
                    'beneficiario',
                    'modulo.programa'
                ])->where('modulo_id',$modulo_id)
                ->latest('id')
                ->whereYear('created_at',$year)
                ->get();

            return response($beneficiarios_inscritos);
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
