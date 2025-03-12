<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class sedes extends Model
{
    protected $connection = 'gds';
    protected $table = 'SEDES';
    public $timestamps = false;
    protected $appends = ['nombre_completo'];

    protected $fillable = [
        'nombre',
        'direccion',
        'zona_id',
        'distrito_id',
        'estado',
    ];

    // RELACIONES INVERSAS

    public function zona () {
        return $this->belongsTo(zonas::class, 'zona_id');
    }

    public function distrito () {
        return $this->belongsTo(distritos::class, 'distrito_id');
    }

    // MUTADORES

    public function getNombreCompletoAttribute() {
        $nombres = [
            $this->nombre,
            $this->zona->descripcion ?? null,
            $this->distrito->nombre ?? null,
            $this->direccion
        ];

        $nombres = array_filter($nombres, function ($nombre) {
            return !is_null($nombre) && $nombre !== '';
        });
        
        return mb_strtoupper(implode(' ', $nombres));
    }

}
