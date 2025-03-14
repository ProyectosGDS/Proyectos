<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class tipos_eventos extends Model
{
    protected $connection = 'gds';
    protected $table = 'TIPOS_EVENTOS';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];
}
