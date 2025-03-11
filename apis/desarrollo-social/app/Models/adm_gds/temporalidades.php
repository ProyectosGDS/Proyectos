<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class temporalidades extends Model
{
    protected $connection = 'gds';
    protected $table = 'TEMPORALIDADES';
    public $timestamps = false;
}
