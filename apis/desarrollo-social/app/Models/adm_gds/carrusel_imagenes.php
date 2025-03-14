<?php

namespace App\Models\adm_gds;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class carrusel_imagenes extends Model
{
    protected $connection = 'gds';
    protected $table = 'CARRUSEL_IMAGENES';
    public $timestamps = false;
    protected $appends = ['url'];

    protected $fillable = [
        'nombre',
        'link',
        'path'
    ];

    public function getUrlAttribute() {
        $url = Storage::url($this->path);
        return $url;
    }

}
