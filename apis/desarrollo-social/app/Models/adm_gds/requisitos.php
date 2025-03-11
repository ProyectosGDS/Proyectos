<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class requisitos extends Model
{
    protected $connection = 'gds';
    protected $table = 'REQUISITOS';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado'
    ];
}
