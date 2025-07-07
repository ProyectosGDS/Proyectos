<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RenapConsultaResource extends JsonResource
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
            'primer_nombre' => $this->primer_nombre,
            'segundo_nombre' => $this->segundo_nombre,
            'primer_apellido' => $this->primer_apellido,
            'segundo_apellido' => $this->segundo_apellido,
            'sexo' => $this->genero,
            'fecha_nacimiento' => Carbon::createFromFormat('d/m/Y', $this->fecha_nacimiento)->format('Y-m-d'),
        ];
    }
}
