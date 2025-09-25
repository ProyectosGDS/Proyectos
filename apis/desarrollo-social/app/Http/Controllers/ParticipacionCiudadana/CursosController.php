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
                    CAST('CURSO' AS VARCHAR2(100)) AS TIPO
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

                UNION ALL

                SELECT DISTINCT
                    M.ID,
                    M.NOMBRE MODULO_CURSO,
                    S.NOMBRE SEDE,
                    T.NOMBRE TEMPORALIDAD,
                    DC.MODALIDAD,
                    M.ESTADO,
                    M.PUBLICO,
                    CAST('MODULO' AS VARCHAR2(100)) AS TIPO
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
            ";

            $cursos_programas = DB::connection('gds')->select($query);

            return response($cursos_programas);

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
                'requisitos',
                'sede',
                'temporalidad'
            ])->loadCount('beneficiarios');

            return response($modulo);

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