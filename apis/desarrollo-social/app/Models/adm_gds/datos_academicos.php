<?php

namespace App\Models\adm_gds;


use Illuminate\Database\Eloquent\Model;

class datos_academicos extends Model
{
    protected $connection = 'gds';
    protected $table = 'DATOS_ACADEMICOS';
    public $timestamps = false;

    protected $fillable = [
        'establecimiento',
        'tipo',
        'titulo_carrera',
        'escolaridad_id',
        'beneficiario_id',

    ];

    // RELACIONES INVERSAS

    public function beneficiario() {
        return $this->belongsTo(beneficiarios::class);
    }

    public function escolaridad() {
        return $this->belongsTo(escolaridades::class);
    }
}
