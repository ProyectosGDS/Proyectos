<?php

namespace App\Models\adm_gds;

use App\Traits\Jwt;
use Illuminate\Foundation\Auth\User as Authenticatable;

class usuarios extends Authenticatable
{

    use Jwt;

    protected $connection = 'gds';
    protected $table = 'USUARIOS';

    protected $fillable = [
        'cui',
        'password',
        'nombre',
        'dependencia_id',
        'perfil_id',
        'deleted_at'
    ];

    protected $hidden = [
        'password',
    ];

    //RELACIONES INVERSAS

    public function dependencia() {
        return $this->belongsTo(dependencias::class);
    }

    public function perfil() {
        return $this->belongsTo(perfiles::class);
    }

}
