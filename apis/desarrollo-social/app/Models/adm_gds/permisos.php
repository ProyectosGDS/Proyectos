<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class permisos extends Model
{
    protected $connection = 'gds';
    protected $table = 'PERMISOS';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'app',
        'grupo'
    ];

    protected $hidden = [
        'pivot',
    ];
}
