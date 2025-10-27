<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class detalles_cursos extends Model
{
    protected $connection = 'gds';
    protected $table = 'DETALLES_CURSOS';
    public $timestamps = false;
    protected $casts = [
        'fecha_inicial' => 'datetime:Y-m-d',
        'fecha_final' => 'datetime:Y-m-d',
    ];

    protected $fillable = [
        'seccion',
        'capacidad',
        'modalidad',
        'curso_id',
        'sede_id',
        'programa_id',
        'temporalidad_id',
        'estado',
        'fecha_inicial',
        'fecha_final',
        'publico',
        'paga',
    ];

    
    // RELACIONES

    public function tarifas() {
        return $this->hasOne(tarifas_cursos::class,'curso_modulo_id')->where('tipo','CURSO');
    }

    public function horarios() {
        return $this->belongsToMany(horarios::class,'cursos_horarios','detalle_curso_id','horario_id');
    }

    public function instructores() {
        return $this->belongsToMany(instructores::class,'cursos_instructores','detalle_curso_id','instructor_id');
    }

    public function modulo() {
        return $this->belongsToMany(modulos::class,'cursos_modulos','detalle_curso_id','modulo_id');
    }

    // RELACIONES INVERSAS

    public function curso() {
        return $this->belongsTo(cursos::class,'curso_id');
    }

    public function programa() {
        return $this->belongsTo(programas::class,'programa_id');
    }


    public function sede() {
        return $this->belongsTo(sedes::class,'sede_id');
    }

    public function temporalidad() {
        return $this->belongsTo(temporalidades::class,'temporalidad_id');
    }

    public function beneficiarios() {
        return $this->belongsToMany(beneficiarios::class,'beneficiarios_cursos','detalle_curso_id','beneficiario_id')->where('BENEFICIARIOS_CURSOS.estado','A');
    }

    public function  requisitos() {
        return $this->belongsToMany(requisitos::class,'requisitos_cursos','detalle_curso_id','requisito_id');
    }

    public function control_asistencia() {
        return $this->belongsToMany(beneficiarios::class,'control_asistencia','curso_modulo_id','beneficiario_id');
    }

}
