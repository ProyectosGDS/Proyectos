<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class cursos extends Model
{
    protected $connection = 'gds';
    protected $table = 'CURSOS';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'impulsatec'
    ];

    // RELACIONES
    public function detalles () {
        return $this->hasMany(detalles_cursos::class,'curso_id');
    }

    public function sedes() {
        return $this->hasManyThrough(sedes::class,detalles_cursos::class,'curso_id','id','id','sede_id');
    }

}
