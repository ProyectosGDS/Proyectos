<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class bitacora extends Model
{
    protected $connection = 'gds';
    protected $table = 'BITACORA';
    public $timestamps = false;

    protected $fillable = [
        'accion',
        'tabla',
        'descripcion',
        'created_at',
        'usuario_id',
        'beneficiario_id',
        'identificador',
    ];

    static $acciones = [
        'ACTUALIZACION DATOS BENEFICIARIO',
        'CREACION BENEFICIARIO',
        'OBSERVACIONES BENEFICIARIO',
        'CAMBIO DE ESTADO BENEFICIARIO',
        'INSCRIPCION BENEFICIARIO A CURSO',
        'HABILITAR INSCRIPCION CURSO',
        'DESHABILITAR INSCRIPCION CURSO',
        'ELIMINAR INSCRIPCION CURSO',
        'INSCRIPCION BENEFICIARIO A MODULO',
        'HABILITAR INSCRIPCION MODULO',
        'DESHABILITAR INSCRIPCION MODULO',
        'ELIMINAR INSCRIPCION MODULO',
        'INSCRIPCION BENEFICIARIO A ACTIVIDAD',
        'HABILITAR INSCRIPCION ACTIVIDAD',
        'DESHABILITAR INSCRIPCION ACTIVIDAD',
        'ELIMINAR INSCRIPCION ACTIVIDAD',
        'INSCRIPCION EN LINEA',
    ];

    // RELACIONES INVERSAS

    public function usuario() {
        return $this->belongsTo(usuarios::class,'usuario_id');
    }

    public function beneficiario() {
        return $this->belongsTo(beneficiarios::class,'beneficiario_id');
    }
}
