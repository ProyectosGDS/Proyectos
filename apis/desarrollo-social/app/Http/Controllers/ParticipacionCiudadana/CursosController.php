<?php

namespace App\Http\Controllers\ParticipacionCiudadana;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\detalles_cursos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CursosController extends Controller
{
    public function index() {
        try {
            
            $query = "
                SELECT
                    DC.ID,
                    I.NOMBRE INSTRUCTOR,
                    T.NOMBRE TEMPORALIDAD,
                    S.NOMBRE SEDE,
                    C.NOMBRE CURSO,
                    P.NOMBRE PROGRAMA,
                    H.HORA_INICIAL||' A '||H.HORA_FINAL HORARIO,
                    ConcatenarDias(LUN, MAR, MIE, JUE, VIE, SAB, DOM) DIAS,
                    TO_CHAR(DC.FECHA_INICIAL,'DD-MM-YYYY')||' - '||TO_CHAR(DC.FECHA_FINAL,'DD-MM-YYYY') FECHAS,
                    DC.MODALIDAD,
                    DC.SECCION,
                    DC.CAPACIDAD,
                    DC.ESTADO,
                    DC.PUBLICO
                FROM DETALLES_CURSOS DC
                    INNER JOIN INSTRUCTORES I
                        ON I.ID = DC.INSTRUCTOR_ID
                    INNER JOIN TEMPORALIDADES T
                        ON T.ID = DC.TEMPORALIDAD_ID
                    INNER JOIN SEDES S
                        ON S.ID = DC.SEDE_ID
                    INNER JOIN HORARIOS H
                        ON H.ID = DC.HORARIO_ID
                    INNER JOIN CURSOS C
                        ON C.ID = DC.CURSO_ID
                    INNER JOIN PROGRAMAS P
                        ON P.ID = DC.PROGRAMA_ID
                WHERE DC.ESTADO = 'A'
                AND DC.PUBLICO = 'S'
                AND EXTRACT(YEAR FROM DC.FECHA_INICIAL) = ?
            ";

            $cursos = DB::connection('gds')->select($query,[date('Y')]);

            return response($cursos);

        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    public function show (detalles_cursos $curso) {
        try {
            return response($curso->load([
                'programa',
                'curso',
                'instructor',
                'temporalidad',
                'horario',
                'sede.zona',
            ])->loadCount('beneficiarios'));

        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }
}
