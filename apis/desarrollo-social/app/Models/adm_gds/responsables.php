<?php

namespace App\Models\adm_gds;


use Illuminate\Database\Eloquent\Model;

class responsables extends Model
{
    protected $connection = 'gds';
    protected $table = 'RESPONSABLES';
    public $timestamps = false;

    protected $casts = [
        'fecha_nacimiento' => 'datetime:Y-m-d',
    ];

    protected $appends = [
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido'
    ];

    protected $fillable = [
        'cui',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'celular',
        'email',
        'sexo',
        'direccion',
        'categoria',
        'beneficiario_id',
        'parentesco_id',
        'zona_id',

    ];

    // RELACIONES INVERSAS

    public function beneficiario() {
        return $this->belongsTo(beneficiarios::class);
    }

    public function parentesco() {
        return $this->belongsTo(parentescos::class);
    }

    public function zona() {
        return $this->belongsTo(zonas::class);
    }

    public function getPrimerNombreAttribute() {
        $nombres = explode(" ",$this->nombres);
        return $nombres[0] ?? '';
    }

    public function getSegundoNombreAttribute() {
        $nombres = explode(" ",$this->nombres);
        return $nombres[1] ?? '';
    }

    public function getPrimerApellidoAttribute() {
        $apellidos = explode(" ",$this->apellidos);
        return $apellidos[0] ?? '';
    }

    public function getSegundoApellidoAttribute() {
        $apellidos = explode(" ",$this->apellidos);
        return $apellidos[1] ?? '';
    }
}
