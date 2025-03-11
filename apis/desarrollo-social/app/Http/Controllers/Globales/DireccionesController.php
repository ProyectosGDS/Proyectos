<?php

namespace App\Http\Controllers\Globales;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\departamentos;
use App\Models\adm_gds\grupos_habitacionales;
use App\Models\adm_gds\grupos_zonas;
use App\Models\adm_gds\municipios;
use App\Models\adm_gds\zonas;
use Illuminate\Http\Request;

class DireccionesController extends Controller
{

    public function zonas() {
        try {

            $zonas = zonas::all();
            return response($zonas);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function grupos_habitacionales() {
        try {

            $grupos_habitacionales = grupos_habitacionales::all();
            return response($grupos_habitacionales);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function grupos_zonas(int $zona_id, int $grupo_habitacional_id) {
        try {

            $grupos_zonas = grupos_zonas::where('zona_id',$zona_id)
                ->where('grupo_habitacional_id',$grupo_habitacional_id)
                ->get();

            return response($grupos_zonas);
            
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function municipios_departamento(int $departamento_id) {
        try {

            $municipios_departamento = municipios::where('departamento_id',$departamento_id)
                ->get();

            return response($municipios_departamento);
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function departamentos() {
        try {
            $departamentos = departamentos::with(['municipios'])->get();
            return response($departamentos);
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
