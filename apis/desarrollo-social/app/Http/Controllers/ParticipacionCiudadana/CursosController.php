<?php

namespace App\Http\Controllers\ParticipacionCiudadana;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\clases_x_cusos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CursosController extends Controller
{
    public function index() {
        try {
            
            $query = "
                SELECT
                    CC.ID_CLASE,
                    I.NOMBRE INSTRUCTOR,
                    T.DESCRIPCION TEMPORALIDAD,
                    S.DESCRIPCION SEDE,
                    C.NOMBRE CURSO,
                    P.NOMBRE PROGRAMA,
                    N.DESCRIPCION NIVEL,
                    CC.MODALIDAD,
                    CC.SECCION,
                    CC.CAPACIDAD,
                    CC.ESTATUS,
                    CC.FECHAU,
                    H.HORA_INI||' A '||H.HORA_FIN ||' '|| ConcatenarDias(LUN, MAR, MIE, JUE, VIE, SAB, DOM) AS FULL
                FROM CLASES_X_CURSO CC
                    INNER JOIN INSTRUCTORES I
                        ON I.ID_INSTRUCTOR = CC.ID_INSTRUCTOR
                    INNER JOIN TEMPORALIDAD T
                        ON T.ID_TEMPORALIDAD = CC.ID_TEMPORALIDAD
                    INNER JOIN SEDES S
                        ON S.ID_SEDE = CC.ID_SEDE
                    INNER JOIN HORARIOS H
                        ON H.ID_HORARIO = CC.ID_HORARIO
                    INNER JOIN CURSOS C
                        ON C.ID_CURSO = CC.ID_CURSO
                    INNER JOIN PROGRAMAS P
                        ON P.ID_PROGRAMA = CC.ID_PROGRAMA
                    INNER JOIN NIVELES N
                        ON N.ID_NIVEL = CC.ID_NIVEL
                WHERE CC.ESTATUS = ?
            ";

            $cursos = DB::connection('oracle_gds')->select($query,['A']);

            return response($cursos);

        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    public function show (clases_x_cusos $clase) {
        try {
            return response($clase->load([
                'programa',
                'nivel',
                'curso.requisitos',
                'instructor',
                'temporalidad',
                'horario',
                'sede.zona',
            ])->loadCount('personas_asignadas'));

        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }
}
