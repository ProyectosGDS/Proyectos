<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class estados_eventos extends Model
{
    protected $connection = 'gds';
    protected $table = 'ESTADOS_EVENTOS';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];
    
}
