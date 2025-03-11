<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;

class perfiles extends Model
{
    protected $connection = 'gds';
    protected $table = 'PERFILES';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'rol_id',
        'menu_id',
    ];

    public function rol() {
        return $this->belongsTo(roles::class);
    }

    public function menu() {
        return $this->belongsTo(menus::class);
    }

}
