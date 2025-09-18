<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class programas extends Model
{
    protected $connection = 'gds';
    protected $table = 'PROGRAMAS';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'dependencia_id',
        'estado',
        'escuela'
    ];

    public static $escuelas = [
        'ESCUELA MUNICIPAL DE ARTES VISUALES',
        'PROGRAMA MUNICIPAL DE DANZA CREATIVA',
        'ESCUELA MUNICIPAL DE ESCULTURA',
        'PROGRAMA MUNICIPAL DE AJEDREZ',
        'ESCUELA MUNICIPAL DE DANZA CLASICA',
        'ESCUELA MUNICIPAL DE MUSICA',
        'CENTRO DE FORMACION TECNOLOGICA',
        'ESCUELA TALLER',
    ];

    // RELACIONES
    public function modulos() {
        return $this->hasMany(modulos::class,'programa_id');
    }

    public function cursos() {
        return $this->hasMany(detalles_cursos::class,'programa_id');
    }

    public function actividades() {
        return $this->hasMany(detalles_actividades::class,'programa_id');
    }

    // RELACIONES INVERSAS
    
    public function dependencia() {
        return $this->belongsTo(dependencias::class,'dependencia_id');
    }
}
