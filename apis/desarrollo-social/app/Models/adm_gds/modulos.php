<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class modulos extends Model
{
    protected $connection = 'gds';
    protected $table = 'MODULOS';
    public $timestamps = false;
    protected $casts = [
        'fecha_inicial' => 'datetime:Y-m-d',
        'fecha_final' => 'datetime:Y-m-d',
    ];

    protected $fillable = [
        'nombre',
        'descripcion',
        'programa_id',
        'estado',
        'fecha_inicial',
        'fecha_final',
        'publico',
        'capacidad',
        
    ];

    // RELACIONES

    public function cursos() {
        return $this->belongsToMany(detalles_cursos::class,'cursos_modulos','modulo_id','detalle_curso_id');
    }

    public function requisitos() {
        return $this->belongsToMany(requisitos::class,'requisitos_modulos','modulo_id','requisito_id');
    }

    public function beneficiarios() {
        return $this->belongsToMany(beneficiarios::class,'beneficiarios_modulos','modulo_id','beneficiario_id')
            ->where('BENEFICIARIOS_MODULOS.estado','A');
    }

    // RELACIONES INVERSAS

    public function programa() {
        return $this->belongsTo(programas::class,'programa_id');
    }

}
