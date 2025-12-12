<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\beneficiarios_actividades;
use App\Models\adm_gds\bitacora;
use App\Models\adm_gds\detalles_actividades;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InscripcionesActividadesController extends Controller
{
    public function update (Request $request, beneficiarios_actividades $inscripcion) {

        $request->validate([
            'beneficiario_id' => 'required',
            'detalle_actividad_id' => 'required',
            'estado' => 'nullable'
        ]);

        try {

            $inscripcion->beneficiario_id = $request->beneficiario_id;
            $inscripcion->detalle_actividad_id = $request->detalle_actividad_id;
            $inscripcion->estado = $request->estado;
            $inscripcion->save();
            
            bitacora::create([
                'accion' => $request->estado == 'A' ? bitacora::$acciones[13] : bitacora::$acciones[14] ,
                'tabla' => 'BENEFICIARIOS_ACTIVIDADES',
                'descripcion' => 'SE CAMBIO DE ESTADO INSCRIPCION',
                'created_at' => now(),
                'usuario_id' => auth()->user()->id,
                'beneficiario_id' => $inscripcion->beneficiario_id,
                'identificador' => $inscripcion->id
            ]);

            return response('Inscripción actualizada correctamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function destroy (beneficiarios_actividades $inscripcion) {

        try {
            
            bitacora::create([
                'accion' => bitacora::$acciones[7],
                'tabla' => 'BENEFICIARIOS_ACTIVIDADES',
                'descripcion' => 'SE ELIMINO REGISTRO INSCRIPCION ID : '.$inscripcion->id .' DETALLE CURSO ID :'.$inscripcion->detalle_actividad_id,
                'created_at' => now(),
                'usuario_id' => auth()->user()->id,
                'beneficiario_id' => $inscripcion->beneficiario_id,
                'identificador' => $inscripcion->id,
            ]);

            $inscripcion->delete();
            
            

            return response('Inscripción eliminada correctamente');
            
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store_beneficiarios(Request $request) {
        $request->validate([
            'beneficiarios' => 'required|array',
            'anio_inscripcion' => 'required|numeric|digits:4',
        ]);

        try {

            $count_beneficiarios = 0;
            
            foreach ($request->beneficiarios as $beneficiario) {

                if(!isset($beneficiario['id'])) {
                    $inscripcion_actividad = beneficiarios_actividades::create([
                        'beneficiario_id' => $beneficiario['beneficiario_id'],
                        'detalle_actividad_id' => $beneficiario['detalle_actividad_id'],
                        'created_at' => now(),
                        'anio_inscripcion' => $request->anio_inscripcion,
                        'estado' => 'A'
                    ]);

                    bitacora::create([
                        'accion' => bitacora::$acciones[12],
                        'tabla' => 'BENEFICIARIOS_ACTIVIDADES',
                        'descripcion' => 'SE INSCRIBIO BENEFICIARIO',
                        'created_at' => now(),
                        'usuario_id' => auth()->user()->id,
                        'beneficiario_id' => $beneficiario['beneficiario_id'],
                        'identificador' => $inscripcion_actividad->id
                    ]);

                    $count_beneficiarios ++;
                }
            }
    
            return response($count_beneficiarios.' Beneficiarios nuevos asignados correctamente');
            
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function get_beneficiarios (int $detalle_actividad_id, string $year) {

        try {

            $beneficiarios_inscritos = beneficiarios_actividades::with([
                    'beneficiario',
                    'actividad.zona',
                    'actividad.distrito',
                    'actividad.actividad',
                    'actividad.tipo',
                    'actividad.estado',
                    'actividad.programa',
                ])->where('detalle_actividad_id',$detalle_actividad_id)
                ->latest('id')
                ->where('anio_inscripcion',$year)
                ->get();

            return response($beneficiarios_inscritos);
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function exportPdf(Request $request) {
        $request->validate([
            'detalle_actividad_id' => 'required|exists:detalles_actividades,id',
            'anio_inscripcion' => 'required|numeric|digits:4',
        ]);

        try {

            $beneficiarios_inscritos = beneficiarios_actividades::with('beneficiario')
                ->where('detalle_actividad_id', $request->detalle_actividad_id)
                ->where('anio_inscripcion', $request->anio_inscripcion)
                ->get()
                ->sortBy(function ($item) {
                    return $item->beneficiario->primer_nombre;
                })
                ->values();

            $header = detalles_actividades::where('id',$request->detalle_actividad_id)
                ->with([
                    'programa',
                    'actividad',
                    'zona',
                    'distrito',
                    'tipo',
                    'estado'
                ])->first();
            
            $pdf = Pdf::loadView('Reports.PdfControlInscritosActividad', compact('header','beneficiarios_inscritos'))->setPaper('a4', 'landscape');

            return $pdf->download('export.pdf');

        } catch (\Throwable $th) {
            
            return response($th->getMessage());
        }
    }
}
