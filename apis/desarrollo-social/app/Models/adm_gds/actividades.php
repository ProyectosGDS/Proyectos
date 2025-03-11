<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class actividades extends Model
{
    protected $connection = 'gds';
    protected $table = 'ACTIVIDADES';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado'
    ];

    // RELACIONES

    public function detalles() {
        return $this->hasMany(detalles_actividades::class, 'actividad_id');
    }

}
