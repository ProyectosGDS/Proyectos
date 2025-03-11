<?php

namespace App\Models\adm_gds;

use DateTime;
use Illuminate\Database\Eloquent\Model;

class beneficiarios extends Model
{
    protected $connection = 'gds';
    protected $table = 'BENEFICIARIOS';
    protected $appends = ['nombre_completo','edad'];
    protected $casts = [
        'fecha_nacimiento' => 'datetime:Y-m-d',
    ];
    protected $fillable = [
        'cui',
        'pasaporte',
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'celular',
        'correo',
        'sexo',
        'fecha_nacimiento',
        'estado_civil_id',
        'etnia_id',
        'interlocutor',
        'deleted_at',
        'estado',
    ];

    //RELACIONES

    public function domicilio() {
        return $this->hasOne(domicilios::class,'beneficiario_id');
    }

    public function datos_medicos() {
        return $this->hasOne(datos_medicos::class,'beneficiario_id');
    }

    public function datos_academicos() {
        return $this->hasOne(datos_academicos::class,'beneficiario_id');
    }

    public function emergencia() {
        return $this->hasOne(responsables::class,'beneficiario_id')->where('categoria','E');
    }

    public function responsable() {
        return $this->hasOne(responsables::class,'beneficiario_id')->where('categoria','R');
    }

    public function observaciones() {
        return $this->hasMany(bitacora::class,'beneficiario_id')->where('accion',bitacora::$acciones[2])->orderBy('id','desc');
    }

    public function acciones() {
        return $this->hasMany(bitacora::class,'beneficiario_id')->whereNotIn('accion',[bitacora::$acciones[2]])->orderBy('id','desc');
    }

    public function cursos() {
        return $this->belongsToMany(detalles_cursos::class,'beneficiarios_cursos','beneficiario_id','detalle_curso_id');
    }

    public function modulos() {
        return $this->belongsToMany(modulos::class,'beneficiarios_modulos','beneficiario_id','modulo_id');
    }

    // MUTADORES

    public function getNombreCompletoAttribute() {
        $nombres = [
            $this->primer_nombre,
            $this->segundo_nombre,
            $this->tercer_nombre,
            $this->primer_apellido,
            $this->segundo_apellido,
            $this->apellido_casada,
        ];

        $nombres = array_filter($nombres, function ($nombre) {
            return !is_null($nombre) && $nombre !== '';
        });
        
        return implode(' ', $nombres);
    }

    public function getEdadAttribute() {
        $fechaNacimiento = new DateTime($this->fecha_nacimiento);
        $fechaActual = new DateTime();
        $edad = $fechaNacimiento->diff($fechaActual);
        return $edad->y;
    }


}
