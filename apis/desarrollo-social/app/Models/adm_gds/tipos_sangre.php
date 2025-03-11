<?php

namespace App\Models\adm_gds;


use Illuminate\Database\Eloquent\Model;

class tipos_sangre extends Model
{
    protected $connection = 'gds';
    protected $table = 'TIPOS_SANGRE';
    public $timestamps = false;
}
