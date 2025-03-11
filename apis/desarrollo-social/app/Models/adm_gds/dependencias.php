<?php

namespace App\Models\adm_gds;


use Illuminate\Database\Eloquent\Model;

class dependencias extends Model
{
    protected $connection = 'gds';
    protected $table = 'DEPENDENCIAS';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    // RELACIONES

    public function programas() {
        return $this->hasMany(programas::class,'dependencia_id');
    }

}
