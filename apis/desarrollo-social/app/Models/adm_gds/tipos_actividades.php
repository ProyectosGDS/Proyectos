<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class tipos_actividades extends Model
{
    protected $connection = 'gds';
    protected $table = 'TIPOS_ACTIVIDADES';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];
}
