<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class roles extends Model
{
    protected $connection = 'gds';
    protected $table = 'ROLES';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function permisos() {
        return $this->belongsToMany(permisos::class,'permisos_roles','rol_id','permiso_id');
    }

}
