<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class eventos extends Model
{
    protected $connection = 'gds';
    protected $table = 'EVENTOS';
    protected $appends = ['fechas','horario'];
    protected $casts = [
        'fecha_inicial' => 'datetime:Y-m-d',
        'fecha_final' => 'datetime:Y-m-d'
    ];

    protected $fillable = [
        'nombre',
        'descripcion',
        'ubicacion',
        'fecha_inicial',
        'fecha_final',
        'hora_inicial',
        'hora_final',
        'responsable',
        'estado_evento_id',
        'tipo_evento_id',
        'dependencia_id',
        'usuario_id',
    ];

    // RELACIONES INVERSAS

    public function dependencia() {
        return $this->belongsTo(dependencias::class,'dependencia_id');
    }

    public function tipo() {
        return $this->belongsTo(tipos_eventos::class,'tipo_evento_id');
    }

    public function estado() {
        return $this->belongsTo(estados_eventos::class,'estado_evento_id');
    }

    public function usuario() {
        return $this->belongsTo(usuarios::class,'usuario_id');
    }

    // MUTADORES

    public function getFechasAttribute() {
        return date('d-m-Y',strtotime($this->fecha_inicial)).' - '.date('d-m-Y',strtotime($this->fecha_final));
    }

    public function getHorarioAttribute() {
        return $this->hora_inicial.' A '.$this->hora_final;
    }

}
