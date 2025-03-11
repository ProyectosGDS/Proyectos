<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class beneficiarios_modulos extends Model
{
    protected $connection = 'gds';
    protected $table = 'BENEFICIARIOS_MODULOS';
    public $timestamps = false;

    protected $fillable = [
        'beneficiario_id',
        'modulo_id',
        'created_at',
        'estado',
    ];


    // RELACIONES INVERSAS

    public function beneficiario() {
        return $this->belongsTo(beneficiarios::class,'beneficiario_id');
    }

    public function modulo() {
        return $this->belongsTo(modulos::class,'modulo_id');
    }
}
