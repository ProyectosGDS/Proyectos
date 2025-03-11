<?php

namespace App\Models\adm_gds;


use Illuminate\Database\Eloquent\Model;

class grupos_zonas extends Model
{
    protected $connection = 'gds';
    protected $table = 'GRUPOS_ZONAS';
    public $timestamps = false;
}
