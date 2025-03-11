<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class instructores extends Model
{
    protected $connection = 'gds';
    protected $table = 'INSTRUCTORES';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado'
    ];

    // RELACIONES
    public function cursos () {
        return $this->hasMany(detalles_cursos::class,'instructor_id');
    }

}
