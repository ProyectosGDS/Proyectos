<?php

namespace App\Models\adm_gds;


use Illuminate\Database\Eloquent\Model;

class datos_medicos extends Model
{
    protected $connection = 'gds';
    protected $table = 'DATOS_MEDICOS';
    public $timestamps = false;

    protected $fillable = [
        'enfermedades_alergias',
        'medicamentos',
        'dosis',
        'beneficiario_id',
        'tipo_sangre_id',
    ];

    // RELACIONES INVERSAS

    public function beneficiario() {
        return $this->belongsTo(beneficiarios::class);
    }

    public function tipo_sangre() {
        return $this->belongsTo(tipos_sangre::class);
    }
}
