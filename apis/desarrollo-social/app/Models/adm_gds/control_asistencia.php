<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class control_asistencia extends Model
{
    protected $connection = 'gds';
    protected $table = 'CONTROL_ASISTENCIA';

    protected $fillable = [
        'curso_modulo_id',
        'beneficiario_id',
        'tipo',
        'fecha',
        'estado',
    ];

    // RELACIONES INVERSAS
    public function beneficiario() {
        return $this->belongsTo(beneficiarios::class, 'beneficiario_id');
    }

    public function curso() {
        return $this->belongsTo(detalles_cursos::class, 'curso_modulo_id');
    }

    public function modulo() {
        return $this->belongsTo(modulos::class, 'curso_modulo_id');
    }

}
