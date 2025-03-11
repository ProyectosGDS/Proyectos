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
        'estado'
    ];

    // RELACIONES
    public function detalles () {
        return $this->hasMany(detalles_cursos::class,'curso_id');
    }

}
