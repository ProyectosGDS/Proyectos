<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class detalles_actividades extends Model
{
    protected $connection = 'gds';
    protected $table = 'DETALLES_ACTIVIDADES';
    public $timestamps = false;
    protected $appends = ['horario','fechas'];

    protected $fillable = [
        'responsable',
        'direccion',
        'hora_inicio',
        'hora_final',
        'fecha_inicial',
        'fecha_final',
        'coordenadas',
        'zona_id',
        'distrito_id',
        'actividad_id',
        'tipo_actividad_id',
        'estado_actividad_id',
        'programa_id',
    ];

    // RELACIONES INVERSAS

    public function actividad() {
        return $this->belongsTo(actividades::class, 'actividad_id');
    }

    public function zona() {
        return $this->belongsTo(zonas::class, 'zona_id');
    }

    public function distrito() {
        return $this->belongsTo(distritos::class, 'distrito_id');
    }

    public function tipo() {
        return $this->belongsTo(tipos_actividades::class, 'tipo_actividad_id');
    }

    public function estado() {
        return $this->belongsTo(estados_actividades::class, 'estado_actividad_id');
    }

    public function programa() {
        return $this->belongsTo(programas::class, 'programa_id');
    }

    // MUTADORES  
    
    public function getHorarioAttribute() {
        if($this->hora_inicio && $this->hora_final) {
            return $this->hora_inicio.' A '.$this->hora_final;
        }

        return null;
    }

    public function getFechasAttribute() {
        if($this->fecha_inicial && $this->fecha_final) {
            return $this->fecha_inicial.' - '.$this->fecha_final;
        }

        return null;
    }

}
