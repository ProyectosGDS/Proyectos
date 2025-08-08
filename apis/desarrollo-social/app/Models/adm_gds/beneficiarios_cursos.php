<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class beneficiarios_cursos extends Model
{
    protected $connection = 'gds';
    protected $table = 'BENEFICIARIOS_CURSOS';
    public $timestamps = false;

    protected $fillable = [
        'beneficiario_id',
        'detalle_curso_id',
        'created_at',
        'estado',
    ];


    // RELACIONES INVERSAS

    public function beneficiario() {
        return $this->belongsTo(beneficiarios::class,'beneficiario_id');
    }

    public function curso() {
        return $this->belongsTo(detalles_cursos::class,'detalle_curso_id');
    }
}
