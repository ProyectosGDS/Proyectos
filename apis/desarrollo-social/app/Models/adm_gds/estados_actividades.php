<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class estados_actividades extends Model
{
    protected $connection = 'gds';
    protected $table = 'ESTADOS_ACTIVIDADES';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

}
