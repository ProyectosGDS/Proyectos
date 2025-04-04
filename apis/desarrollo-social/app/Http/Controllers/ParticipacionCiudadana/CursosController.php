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
            
            $query = "
                SELECT
                    DC.ID,
                    C.NOMBRE MODULO_CURSO,
                    S.NOMBRE SEDE,
                    T.NOMBRE TEMPORALIDAD,
                    DC.MODALIDAD,
                    DC.ESTADO,
                    DC.PUBLICO,
                    'CURSO' TIPO
                FROM DETALLES_CURSOS DC
                    INNER JOIN TEMPORALIDADES T
                        ON T.ID = DC.TEMPORALIDAD_ID
                    INNER JOIN SEDES S
                        ON S.ID = DC.SEDE_ID
                    INNER JOIN CURSOS C
                        ON C.ID = DC.CURSO_ID
                WHERE DC.ESTADO = 'A'
                AND DC.PUBLICO = 'S'
                AND EXTRACT(YEAR FROM DC.FECHA_INICIAL) = ?

                UNION ALL

                SELECT DISTINCT
                    M.ID,
                    M.NOMBRE MODULO_CURSO,
                    S.NOMBRE SEDE,
                    T.NOMBRE TEMPORALIDAD,
                    DC.MODALIDAD,
                    M.ESTADO,
                    M.PUBLICO,
                    'MODULO' TIPO
                FROM MODULOS M
                    INNER JOIN CURSOS_MODULOS CM
                        ON CM.MODULO_ID = M.ID
                    INNER JOIN DETALLES_CURSOS DC
                        ON CM.DETALLE_CURSO_ID = DC.ID
                    INNER JOIN SEDES S
                        ON DC.SEDE_ID = S.ID
                    INNER JOIN TEMPORALIDADES T
                        ON DC.TEMPORALIDAD_ID = T.ID
                WHERE M.ESTADO = 'A'
                AND M.PUBLICO = 'S'
                AND EXTRACT(YEAR FROM M.FECHA_INICIAL) = ?
            ";

            $cursos = DB::connection('gds')->select($query,[date('Y'),date('Y')]);

            return response($cursos);

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
