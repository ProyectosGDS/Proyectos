<?php

namespace App\Http\Controllers\Globales;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\actividades;
use App\Models\adm_gds\cursos;
use App\Models\adm_gds\departamentos;
use App\Models\adm_gds\distritos;
use App\Models\adm_gds\escolaridades;
use App\Models\adm_gds\estados_actividades;
use App\Models\adm_gds\estados_civiles;
use App\Models\adm_gds\etnias;
use App\Models\adm_gds\grupos_habitacionales;
use App\Models\adm_gds\horarios;
use App\Models\adm_gds\instructores;
use App\Models\adm_gds\parentescos;
use App\Models\adm_gds\sedes;
use App\Models\adm_gds\temporalidades;
use App\Models\adm_gds\tipos_actividades;
use App\Models\adm_gds\tipos_sangre;
use App\Models\adm_gds\zonas;
use Illuminate\Http\Request;

class CatalogosController extends Controller
{
    public function etnias() {
        try {
            
            $etnias = etnias::all();
            return response($etnias);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function escolaridades() {
        try {
            
            $escolaridades = escolaridades::all();
            return response($escolaridades);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function estados_civiles() {
        try {
            
            $estados_civiles = estados_civiles::all();
            return response($estados_civiles);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function tipos_sangre() {
        try {
            
            $tipos_sangre = tipos_sangre::all();
            return response($tipos_sangre);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function parentescos() {
        try {

            $parentescos = parentescos::all();
            return response($parentescos);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function catalogosBeneficiario() {
        try {

            $catalogo = [
                'departamentos' => departamentos::with(['municipios'])->get(),
                'grupos_habitacionales' => grupos_habitacionales::all(),
                'etnias' => etnias::all(),
                'escolaridades' => escolaridades::all(),
                'estados_civiles' => estados_civiles::all(),
                'tipos_sangre' => tipos_sangre::all(),
                'parentescos' => parentescos::all(),
                'zonas' => zonas::all(),
            ];

            return response($catalogo);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function catalogosCurso() {
        try {
            
            return response([
                'cursos' => cursos::where('estado','A')->orderBy('nombre','asc')->get(),
                'sedes' => sedes::where('estado','A')->orderBy('nombre','asc')->get(),
                'instructores' => instructores::where('estado','A')->orderBy('nombre','asc')->get(),
                'horarios' => horarios::where('estado','A')->orderBy('hora_inicial','asc')->get(),
                'temporalidades' => temporalidades::all(),
            ]);

        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    public function catalogosActividad() {
        try {
            return response([
                'zonas' => zonas::all(),
                'distritos' => distritos::all(),
                'tipos_actividades' => tipos_actividades::all(),
                'actividades' => actividades::orderBy('nombre','asc')->get(),
                'estados_actividades' => estados_actividades::all()
            ]);

        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }
}
