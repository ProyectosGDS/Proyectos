<?php

namespace App\Models\adm_gds;


use Illuminate\Database\Eloquent\Model;

class responsables extends Model
{
    protected $connection = 'gds';
    protected $table = 'RESPONSABLES';
    public $timestamps = false;

    protected $fillable = [
        'cui',
        'nombre',
        'celular',
        'email',
        'sexo',
        'direccion',
        'categoria',
        'beneficiario_id',
        'parentesco_id',
        'zona_id',

    ];

    // RELACIONES INVERSAS

    public function beneficiario() {
        return $this->belongsTo(beneficiarios::class);
    }

    public function parentesco() {
        return $this->belongsTo(parentescos::class);
    }

    public function zona() {
        return $this->belongsTo(zonas::class);
    }
}
