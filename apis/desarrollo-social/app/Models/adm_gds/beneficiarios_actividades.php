<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class beneficiarios_actividades extends Model
{
    protected $connection = 'gds';
    protected $table = 'BENEFICIARIOS_ACTIVIDADES';
    public $timestamps = false;

    protected $fillable = [
        'beneficiario_id',
        'detalle_actividad_id',
        'created_at',
        'estado',
    ];


    // RELACIONES INVERSAS

    public function beneficiario() {
        return $this->belongsTo(beneficiarios::class,'beneficiario_id');
    }

    public function actividad() {
        return $this->belongsTo(detalles_actividades::class,'detalle_actividad_id');
    }
}
