<?php

namespace App\Http\Controllers\ParticipacionCiudadana;

use App\Http\Controllers\Controller;
use App\Http\Resources\BeneficiarioUnicoResource;
use App\Http\Resources\RenapConsultaResource;
use App\Models\adm_gds\beneficiarios as Adm_gdsBeneficiarios;
use App\Models\adm_gds\renap_consultas;
use App\Models\Muni\TbBeneficiarioUnico;
use App\Rules\ValidateCui;
use Illuminate\Http\Request;

class Beneficiarios extends Controller
{

    public function  searchBeneficiario(Request $request) {
        $request->validate([
            'cui' => ['required','numeric','digits:13',new ValidateCui ],
        ]);

        try {

            $beneficiarioUnico = Adm_gdsBeneficiarios::with([
                'domicilio',
                'datos_medicos',
                'datos_academicos',
                'responsable',
                'emergencia',
                'cursos' => function($query) {
                    $query->where('programa_id',62)
                        ->whereDate('fecha_inicial','>=','2026-05-02')
                        ->whereDate('fecha_final','<=','2026-07-31');
                },
            ])->where('cui',$request->cui)
            ->first();

            if(!$beneficiarioUnico){

                $beneficiarioUnico = TbBeneficiarioUnico::where('cui',$request->cui)->first();

                if($beneficiarioUnico) {
                    return response([
                        'message' => 'Se encontro información en la base de datos antigua.',
                        'success' => true,
                        'data' => BeneficiarioUnicoResource::make($beneficiarioUnico),
                        'code' => 4,
                    ]);
                }

                $beneficiarioUnico = renap_consultas::where('cui',$request->cui)->first();

                if($beneficiarioUnico) {
                    return response([
                        'message' => 'Se encontro información en la base de datos antigua.',
                        'success' => true,
                        'data' => RenapConsultaResource ::make($beneficiarioUnico['data']),
                        'code' => 5
                    ]);
                } else {
                    return response([
                        'message' => 'Beneficiario nuevo',
                        'success' => false,
                        'data' => [],
                        'code' => 6,
                    ],422);
                }

            }

            return response([
                'message' => 'El cui ya existe en la base de datos.',
                'success' => true,
                'data' => $beneficiarioUnico,
                'code' => 2,
            ]);

        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage(),
                'success' => false,
                'data' => [],
                'code' => $th->getMessage() === 'Respuesta no esperada de RENAP: EL CUI INGRESADO NO CORRESPONDE A UNA PERSONA MAYOR DE EDAD' ? 7 : 1,
            ]);
        }
    }
}
