<?php

namespace App\Models\adm_gds;


use Illuminate\Database\Eloquent\Model;

class grupos_habitacionales extends Model
{
    protected $connection = 'gds';
    protected $table = 'GRUPOS_HABITACIONALES';
    public $timestamps = false;

}
