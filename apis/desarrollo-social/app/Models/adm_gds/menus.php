<?php

namespace App\Models\adm_gds;


use Illuminate\Database\Eloquent\Model;

class menus extends Model
{
    protected $connection = 'gds';
    protected $table = 'MENUS';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    public function paginas() {
        return $this->belongsToMany(paginas::class,'paginas_menus','menu_id','pagina_id');
    }
}
