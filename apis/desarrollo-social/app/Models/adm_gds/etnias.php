<?php

namespace App\Models\adm_gds;


use Illuminate\Database\Eloquent\Model;

class etnias extends Model
{
    protected $connection = 'gds';
    protected $table = 'ETNIAS';
    public $timestamps = false;
}
