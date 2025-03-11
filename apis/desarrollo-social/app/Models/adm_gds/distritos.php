<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class distritos extends Model
{
    protected $connection = 'gds';
    protected $table = 'DISTRITOS';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    // RELACIONES 

    public function sedes () {
        return $this->hasMany(sedes::class, 'distrito_id');
    }



}
