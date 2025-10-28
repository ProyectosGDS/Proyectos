<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class tarifas_cursos extends Model
{
    protected $connection = 'gds';
    protected $table = 'TARIFAS_CURSOS';
    public $timestamps = false;
    protected $casts = [
        'inscripcion' => 'decimal:2',
        'tarifa_menor' => 'decimal:2',
        'tarifa_mayor' => 'decimal:2',
    ];


    protected $fillable = [
        'tipo',
        'curso_modulo_id',
        'inscripcion',
        'tarifa_menor',
        'tarifa_mayor',
        'temporalidad',
        'no_cuotas',
        'mes_inicial',
        'mes_final',
    ];

    public static $temporalidad = [
        'MENSUAL',
        'TRIMESTRAL',
        'ANUAL'
    ];
   
    public function curso() {
        return $this->belongsTo(cursos::class,'curso_modulo_id');
    }

    public function modulo() {
        return $this->belongsTo(modulos::class,'curso_modulo_id');
    }
}
