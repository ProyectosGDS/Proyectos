<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class horarios extends Model
{
    protected $connection = 'gds';
    protected $table = 'HORARIOS';
    public $timestamps = false;
    protected $appends = ['hora', 'dias', 'nombre_completo'];

    protected $fillable = [
        'hora_inicial',
        'hora_final',
        'lun',
        'mar',
        'mie',
        'jue',
        'vie',
        'sab',
        'dom',
        'estado',

    ];

    // RELACIONES
    public function cursos () {
        return $this->hasMany(detalles_cursos::class,'horario_id');
    }

    // MUTADORES

    public function getHoraAttribute () {
        return $this->hora_inicial . ' A ' . $this->hora_final;
    }

    public function getDiasAttribute () {
        $dias = [
            $this->lun,
            $this->mar,
            $this->mie,
            $this->jue,
            $this->vie,
            $this->sab,
            $this->dom,
        ];

        $dias = array_filter($dias, function ($dia) {
            return !is_null($dia) && $dia !== '';
        });

        return implode(', ', $dias);
    }

    public function getNombreCompletoAttribute() {
        return mb_strtoupper($this->hora .' - '. $this->dias);
    }

}
