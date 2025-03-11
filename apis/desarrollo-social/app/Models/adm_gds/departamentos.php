<?php

namespace App\Models\adm_gds;


use Illuminate\Database\Eloquent\Model;

class departamentos extends Model
{
    protected $connection = 'gds';
    protected $table = 'DEPARTAMENTOS';
    public $timestamps = false;

    public function municipios() {
        return $this->hasMany(municipios::class,'departamento_id');
    }

}
