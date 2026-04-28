<?php

namespace App\Http\Controllers\ParticipacionCiudadana;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\cursos;
use App\Models\adm_gds\modulos;
use App\Models\adm_gds\programas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CursosController extends Controller
{
    public function index(Request $request) {
        $this->validate($request, [
            'tipo' => 'required|in:CURSO,PROGRAMA'
        ]);
        try {
            $query ="";

            if($request->tipo == 'CURSO') {
                $query = "
                    SELECT
                        DC.CURSO_ID ID,
                        C.NOMBRE CURSO,
                        C.DESCRIPCION,
                        'CURSO' TIPO
                    FROM ADM_GDS.DETALLES_CURSOS DC
                        INNER JOIN ADM_GDS.CURSOS C
                            ON DC.CURSO_ID = C.ID
                    WHERE DC.MODALIDAD IS NOT NULL
                    AND DC.PUBLICO = 'S'
                    AND DC.ESTADO = 'A'
                    AND DC.PROGRAMA_ID = 62
                    AND DC.FECHA_INICIAL >= TO_DATE('2026-05-02', 'YYYY-MM-DD')
                    AND DC.FECHA_FINAL <= TO_DATE('2026-08-02', 'YYYY-MM-DD')
                    GROUP BY 
                        DC.CURSO_ID, 
                        C.NOMBRE,
                        C.DESCRIPCION
                    ORDER BY 2 ASC
                ";

            }

            if($request->tipo == 'PROGRAMA') {
                $query = "
                    SELECT
                        P.ID,
                        P.NOMBRE PROGRAMA
                    FROM ADM_GDS.MODULOS M
                        INNER JOIN ADM_GDS.PROGRAMAS P
                            ON M.PROGRAMA_ID =  P.ID
                    WHERE P.ESTADO = 'A'
                    GROUP BY
                        P.ID,
                        P.NOMBRE
                    ORDER BY 2
                ";
            }

            
            if(!empty($query)) {
                $cursos_programas = DB::connection('gds')->select($query);
                return response($cursos_programas);
            }



        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    public function getCurso (cursos $curso) {
        try {
            $filter_curso = $curso->load([
                'detalles' => function($query) {
                    $query->whereDate('fecha_inicial','>=','2026-05-02')
                        ->whereDate('fecha_final','<=','2026-08-02');
                },
                'detalles.instructores',
                'detalles.temporalidad',
                'detalles.horarios',
                'detalles.programa',
                'detalles.sede',
                'detalles.requisitos',
                'detalles.beneficiariosTodos' => function($query) {
                    $query->wherePivot('anio_inscripcion','=',2026);
                }
            ]);

            $detalles = $filter_curso->detalles->filter(function($detalle){
                if($detalle->cupos_disponibles > 0) {
                    return $detalle;
                }
            });

            unset($filter_curso->detalles);
            $filter_curso->detalles = $detalles->values();

            return response($filter_curso);

        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    public function getModulo (modulos $modulo) {
        try {
            $modulo = $modulo->load([
                'cursos.curso',
                'requisitos',
                'sede',
                'temporalidad'
            ])->loadCount('beneficiarios');

            return response($modulo);

        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    public function getPrograma(programas $programa) {
        try {
            return response($programa->load([
                'modulos.cursos.curso',
                'modulos.requisitos',
                'modulos.sede',
                'modulos.temporalidad'
            ]));
        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }
}

// QUERY PARA AGRUPAR TODOS LOS CURSOS Y MODULOS
/*

SELECT
		DC.CURSO_ID ID,
		C.NOMBRE MODULO_CURSO,
		T.NOMBRE TEMPORALIDAD,
		DC.MODALIDAD,
		DC.ESTADO,
		DC.PUBLICO,
		CAST('CURSO' AS VARCHAR2(100)) AS TIPO,
		COUNT(DC.SEDE_ID) CNT_SEDE
FROM ADM_GDS.DETALLES_CURSOS DC
		INNER JOIN ADM_GDS.TEMPORALIDADES T
				ON T.ID = DC.TEMPORALIDAD_ID
		INNER JOIN ADM_GDS.SEDES S
				ON S.ID = DC.SEDE_ID
		INNER JOIN ADM_GDS.CURSOS C
				ON C.ID = DC.CURSO_ID
WHERE DC.ESTADO = 'A'
AND DC.PUBLICO = 'S'
AND TO_CHAR(DC.FECHA_FINAL,'YYYY-MM-DD') >= TO_CHAR(SYSDATE,'YYYY-MM-DD')
AND EXTRACT(YEAR FROM DC.FECHA_FINAL) = EXTRACT(YEAR FROM SYSDATE)
GROUP BY DC.CURSO_ID, C.NOMBRE,T.NOMBRE,DC.MODALIDAD,DC.ESTADO,DC.PUBLICO

UNION ALL

SELECT DISTINCT
		M.ID,
		M.NOMBRE MODULO_CURSO,
		T.NOMBRE TEMPORALIDAD,
		M.MODALIDAD,
		M.ESTADO,
		M.PUBLICO,
		CAST('MODULO' AS VARCHAR2(100)) AS TIPO,
		COUNT(DC.SEDE_ID) CNT_SEDE
FROM ADM_GDS.MODULOS M
		INNER JOIN ADM_GDS.CURSOS_MODULOS CM
				ON CM.MODULO_ID = M.ID
		INNER JOIN ADM_GDS.DETALLES_CURSOS DC
				ON CM.DETALLE_CURSO_ID = DC.ID
		INNER JOIN ADM_GDS.SEDES S
				ON DC.SEDE_ID = S.ID
		INNER JOIN ADM_GDS.TEMPORALIDADES T
				ON DC.TEMPORALIDAD_ID = T.ID
WHERE M.ESTADO = 'A'
AND M.PUBLICO = 'S'
AND TO_CHAR(M.FECHA_FINAL,'YYYY-MM-DD') >= TO_CHAR(SYSDATE,'YYYY-MM-DD')
AND EXTRACT(YEAR FROM M.FECHA_FINAL) = EXTRACT(YEAR FROM SYSDATE)
GROUP BY M.ID, M.NOMBRE,T.NOMBRE,M.MODALIDAD,M.ESTADO,M.PUBLICO

*/


// QUERY PARA CONSULTAR SEGUN EL CURSO O MODULO LAS SEDES AGRUPADAS

/*

SELECT
	DC.CURSO_ID,
	C.NOMBRE CURSO,
	DC.SEDE_ID,
	S.NOMBRE SEDE,
	S.ZONA_ID ZONA
FROM DETALLES_CURSOS DC
	INNER JOIN CURSOS C
		ON DC.CURSO_ID = C.ID
	INNER JOIN SEDES S
		ON DC.SEDE_ID = S.ID
WHERE C.ID = 21
GROUP BY DC.CURSO_ID, C.NOMBRE, DC.SEDE_ID, S.NOMBRE, S.ZONA_ID
ORDER BY DC.SEDE_ID

*/


//QUERY PARA CONSULTAR LAS SECCIONES SEGUN LA EL CURSO Y LA SEDE SI TIENE PARA INSCRIBIRSE

/*
SELECT
	DC.ID,
	DC.CURSO_ID,
	C.NOMBRE CURSO,
	DC.SEDE_ID,
	S.NOMBRE SEDE,
	S.DIRECCION,
	S.ZONA_ID ZONA,
	SECCION,
	H.HORA_INICIAL || '--'||H.HORA_FINAL HORARIOS,
	CONCATENARDIAS(H.LUN,H.MAR,H.MIE,H.JUE,H.VIE,H.SAB,H.DOM) DIAS
FROM DETALLES_CURSOS DC
	INNER JOIN CURSOS C
		ON DC.CURSO_ID = C.ID
	INNER JOIN SEDES S
		ON DC.SEDE_ID = S.ID
	INNER JOIN CURSOS_HORARIOS CH
		ON CH.DETALLE_CURSO_ID = DC.ID
	INNER JOIN HORARIOS H
		ON CH.HORARIO_ID = H.ID
WHERE C.ID = 21
AND DC.SEDE_ID = 122

*/