<?php

namespace App\Models\adm_gds;

use App\Traits\Jwt;
use Illuminate\Foundation\Auth\User as Authenticatable;

class usuarios extends Authenticatable
{

    use Jwt;

    protected $connection = 'gds';
    protected $table = 'USUARIOS';

    protected $fillable = [
        'cui',
        'password',
        'nombre',
        'dependencia_id',
        'perfil_id',
        'deleted_at'
    ];

    protected $hidden = [
        'password',
    ];

    protected $appends = ['permisos','menu','nombre_perfil'];

    //RELACIONES INVERSAS

    public function dependencia() {
        return $this->belongsTo(dependencias::class);
    }

    public function perfil() {
        return $this->belongsTo(perfiles::class);
    }

    public function getNombrePerfilAttribute($key) {
        return $this->perfil->nombre;
    }

    public function getPermisosAttribute() {

        $appHeader = request()->header('App'); // Obtener el header de la app
        $permisos = [];

        if ($this->perfil && $this->perfil->rol) {
            foreach ($this->perfil->rol->permisos as $permiso) {
                if ($permiso->app === $appHeader) {
                    $permisos[] = $permiso->nombre;
                }
            }
        }

        return $permisos;
    }

    public function getMenuAttribute() {

        if ($this->perfil->menu && $this->perfil->menu->paginas) {
            $paginas = $this->perfil->menu->paginas->load('padre');
            $grupoPaginas = $paginas->groupBy('pagina_id');
            
            $menu = collect();
            $childrens = collect();

            foreach ($grupoPaginas as $grupo) {
                foreach ($grupo as $hijo) {
                    if ($hijo->padre) {
                        $menu->push($hijo->padre);
                    } else {
                        $menu->push($hijo);
                    }
                    unset($hijo->padre, $hijo->pivot);
                    $childrens->push($hijo);
                }
            }
            $menu = $menu->unique('id');
            $menu->each(function ($padre) use ($childrens) {
                $padre->childrens = $childrens->where('pagina_id', $padre->id)->sortBy('orden')->values();
            });
        }
        return $menu->sortBy('orden')->values()->all();
    }

}
