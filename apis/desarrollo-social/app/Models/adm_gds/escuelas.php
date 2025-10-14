<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class escuelas extends Model
{
    protected $connection = 'gds';
    protected $table = 'ESCUELAS';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'objeto_contrato',
        'dependencia_id',
    ];

    //RELACIONES
    public function programas() {
        return $this->hasMany(programas::class,'escuela_id');
    }


    // RELACIONES INVERSAS
    
    public function dependencia() {
        return $this->belongsTo(dependencias::class,'dependencia_id');
    }
}
