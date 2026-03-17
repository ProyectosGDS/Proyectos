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

    protected $appends = ['cupos_disponibles'];

    protected $fillable = [
        'nombre',
        'descripcion',
        'programa_id',
        'sede_id',
        'temporalidad_id',
        'modalidad',
        'seccion',
        'estado',
        'fecha_inicial',
        'fecha_final',
        'publico',
        'capacidad',
        'paga',
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
            ->where('BENEFICIARIOS_MODULOS.estado','A')->withPivot('becado','anio_inscripcion');
    }
    public function beneficiariosTodos() {
        return $this->belongsToMany(beneficiarios::class,'beneficiarios_modulos','modulo_id','beneficiario_id')
            ->withPivot('becado','anio_inscripcion');
    }

    public function tarifas() {
        return $this->hasOne(tarifas_cursos::class,'curso_modulo_id')->where('tipo','MODULO');
    }

    // RELACIONES INVERSAS

    public function programa() {
        return $this->belongsTo(programas::class,'programa_id');
    }

    public function control_asistencia() {
        return $this->belongsToMany(beneficiarios::class,'control_asistencia','curso_modulo_id','beneficiario_id');
    }

    public function sede() {
        return $this->belongsTo(sedes::class);
    }

    public function temporalidad() {
        return $this->belongsTo(temporalidades::class);
    }

    public function getCuposDisponiblesAttribute() {
        $inscritos = $this->beneficiariosTodos()->count();
        $total = $this->capacidad - $inscritos;
        if($total <= 0) {
            return 0;
        }else {
            return $total;
        }
        
    }


}
