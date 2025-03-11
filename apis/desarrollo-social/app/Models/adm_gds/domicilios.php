<?php

namespace App\Models\adm_gds;


use Illuminate\Database\Eloquent\Model;

class domicilios extends Model
{
    protected $connection = 'gds';
    protected $table = 'DOMICILIOS';
    protected $appends = ['departamento_id','grupo_habitacional_id'];
    public $timestamps = false;

    protected $fillable = [
        'direccion',
        'municipio_id',
        'beneficiario_id',
        'zona_id',
        'grupo_zona_id',

    ];

    public function municipio() {
        return $this->belongsTo(municipios::class,'municipio_id');
    }

    public function grupo_zona() {
        return $this->belongsTo(grupos_zonas::class,'grupo_zona_id');
    }

    public function getDepartamentoIdAttribute() {
        return $this->municipio->departamento_id ?? null;
    }

    public function getGrupoHabitacionalIdAttribute() {
        return $this->grupo_zona->grupo_habitacional_id ?? null;
    }

}
