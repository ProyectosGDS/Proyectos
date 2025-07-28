<?php

namespace App\Http\Controllers\ParticipacionCiudadana;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\detalles_cursos;
use App\Models\adm_gds\modulos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CursosController extends Controller
{
    public function index() {
        try {

            $detalles_cursos = DB::connection('gds')->table('DETALLES_CURSOS DC')
                ->join('TEMPORALIDADES T','T.ID','=','DC.TEMPORALIDAD_ID')
                ->join('SEDES S','S.ID','=','DC.SEDE_ID')
                ->join('CURSOS C','C.ID','=','DC.CURSO_ID')
                ->select(
                    "DC.ID",
                    "C.NOMBRE AS MODULO_CURSO",
                    "S.NOMBRE AS SEDE",
                    "T.NOMBRE AS TEMPORALIDAD",
                    "DC.MODALIDAD",
                    "DC.ESTADO",
                    "DC.PUBLICO",
                    DB::raw("CAST('CURSO' AS VARCHAR2(50)) AS TIPO")
                    
                )
                ->where('DC.ESTADO','A')
                ->where('DC.PUBLICO','S')
                ->whereYear('DC.FECHA_INICIAL',date('Y'));

            $modulos = DB::connection('gds')->table('MODULOS M')
                ->join('TEMPORALIDADES T','T.ID','=','M.TEMPORALIDAD_ID')
                ->join('SEDES S','S.ID','=','M.SEDE_ID')
                ->select(
                    "M.ID",
                    "M.NOMBRE AS MODULO_CURSO",
                    "S.NOMBRE AS SEDE",
                    "T.NOMBRE AS TEMPORALIDAD",
                    "M.MODALIDAD",
                    "M.ESTADO",
                    "M.PUBLICO",
                    DB::raw("CAST('MODULO' AS VARCHAR2(50)) AS TIPO")
                    
                )
                ->where('M.ESTADO','A')
                ->where('M.PUBLICO','S')
                ->whereYear('M.FECHA_INICIAL',date('Y'))
                ->unionAll($detalles_cursos)
                ->get();

            return response($modulos);

        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    public function getCurso (detalles_cursos $curso) {
        try {
            return response($curso->load([
                'programa',
                'curso',
                'instructor',
                'temporalidad',
                'horario',
                'sede.zona',
                'requisitos',
            ])->loadCount('beneficiarios'));

        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    public function getModulo (modulos $modulo) {
        try {
            $modulo = $modulo->load([
                'cursos.curso',
                'requisitos'
            ])->loadCount('beneficiarios');

            $modulo['sede'] = $modulo->cursos[0]->sede;
            $modulo['horario'] = $modulo->cursos[0]->horario;
            $modulo['modalidad'] = $modulo->cursos[0]->modalidad;
            $modulo['capacidad'] = $modulo->capacidad;

            return response($modulo);

        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }
}
