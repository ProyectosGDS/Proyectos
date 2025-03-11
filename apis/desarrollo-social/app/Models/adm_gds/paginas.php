<?php

namespace App\Models\adm_gds;


use Illuminate\Database\Eloquent\Model;

class paginas extends Model
{
    protected $connection = 'gds';
    protected $table = 'PAGINAS';
    public $timestamps = false;
    protected $appends = ['active'];

    protected $fillable = [
        'titulo',
        'link',
        'icon',
        'orden',
        'pagina_id',
    ];

    protected $hidden = [
        'pivot',
    ];

    public function padre() {
        return $this->belongsTo(paginas::class,'pagina_id');
    }

    public function getActiveAttribute(){
        return false;
    }

}
