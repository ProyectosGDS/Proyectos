<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class renap_consultas extends Model
{
    const UPDATED_AT = null;

    protected $connection = 'gds';
    protected $table = 'RENAP_CONSULTAS';

    protected $fillable = [
        'cui',
        'primer_nombre',
        'segundo_nombre',
        'tercer_nombre',
        'primer_apellido',
        'segundo_apellido',
        'apellido_casada',
        'fecha_nacimiento',
        'genero',
        'estado_civil',
        'nacionalidad',
        'pais_nacimiento',
        'depto_nacimiento',
        'muni_nacimiento',
        'vecindad',
        'orden_cedula',
        'registro_cedula',
        'fecha_defuncion',
        'ocupacion',
        'fecha_vencimiento',
        'correlativo_dpi',
    ];
}
