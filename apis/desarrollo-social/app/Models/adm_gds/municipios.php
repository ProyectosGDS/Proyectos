<?php

namespace App\Models\adm_gds;


use Illuminate\Database\Eloquent\Model;

class municipios extends Model
{
    protected $connection = 'gds';
    protected $table = 'MUNICIPIOS';
    public $timestamps = false;

    public function departamento() {
        return $this->belongsTo(departamentos::class,'departamento_id');
    }

}
