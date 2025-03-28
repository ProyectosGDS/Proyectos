<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeneficiarioUnicoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'cui' => $this->cui,
            'pasaporte' => $this->pasaporte_alumno,
            'primer_nombre' => $this->primer_nombre,
            'segundo_nombre' => $this->segundo_nombre,
            'primer_apellido' => $this->primer_apellido,
            'segundo_apellido' => $this->segundo_apellido,
            'sexo' => $this->sexo,
            'fecha_nacimiento' => date('Y-m-d',strtotime($this->fecha_nacimiento)),
            'celular' => $this->celular_alum ?? $this->telefono,
            'correo' => $this->correo_alumno,
            'interlocutor' => $this->interlocutor,
            'datos_academicos' => [
                'tipo' => $this->escuela_tipo,
                'establecimiento' => $this->nombre_escuela,
            ],
            'responsable' => [
                'nombre' => $this->nombre_completo([ $this->primer_nombre_r, $this->segundo_nombre_r, $this->segundo_nombre_r, $this->tercer_nombre_r, $this->primer_apellido_r, $this->segundo_apellido_r ]),
                'cui' => $this->dpi_responsable,
                'email' => $this->correo_respons,
                'celular' => $this->celular_resp ?? $this->telefono_responsable,
            ],
            'emergencia' => [
                'nombre' => $this->responsable_emergencia,
            ]


        ];
    }

    public function nombre_completo(array $nombres) {
        $nombres = $nombres;

        $nombres = array_filter($nombres, function ($nombre) {
            return !is_null($nombre) && $nombre !== '';
        });
        
        return implode(' ', $nombres);
    }
}
