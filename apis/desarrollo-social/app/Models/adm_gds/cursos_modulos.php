<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class cursos_modulos extends Model
{
    protected $connection = 'gds';
    protected $table = 'CURSOS_MODULOS';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'modulo_id',
        'detalle_curso_id'
    ];

    public function modulo() {
        return $this->belongsTo(modulos::class,'modulo_id');
    }

    public function curso() {
        return $this->belongsTo(detalles_cursos::class,'detalle_curso_id');
    }
}
