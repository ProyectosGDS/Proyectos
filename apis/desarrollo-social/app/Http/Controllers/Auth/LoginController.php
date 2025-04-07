<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\usuarios;
use App\Rules\ValidateCui;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function authenticate(Request $request) {
        $request->validate([
            'cui' => ['required','numeric','digits:13',new ValidateCui,'exists:usuarios,cui'],
            'password' => 'required|string|min:8'
        ]);

        try {

            $aud = $request->header('Origin');
            $receivers = config('jwt.receivers');

            if(in_array($aud,$receivers)){

                $user = usuarios::with('dependencia')->where('cui',$request->cui)->whereNull('deleted_at')->first();

                if($user) {
                    $user->makeVisible('password');
                    if(Hash::check(base64_decode($request->password),$user->password)){
    
                        $user->makeHidden('password');
    
                        Auth::login($user);
                        
                        $payload = [
                            'sub' => $user->id,
                        ];
    
                        $accessToken = $user->createToken($payload, $aud);
    
                        if($accessToken) {
    
                            $cookie = cookie(base64_encode('access_token'), $accessToken, config('jwt.expired_token'), '/', null, null, false);
    
                            $user['permisos'] = $this->permisosApp($user,$request->header('App'));
                            $user['menu'] = $this->menu($user);
    
                            $perfil = $user->perfil->nombre;
                            unset($user->perfil);
                            $user['perfil'] = $perfil;
                            $user->makeHidden('perfil_id');
    
                            return response(base64_encode($user))->withCookie($cookie);
                        }
    
                        return response('Unauthorized',422);
                    }
                }

            }

            return response([
                'message' => 'Credenciales invalidas',
                'errors' => [
                    'credenciales' => ['Credenciales invalidas']
                ] 
            ], 422);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }

        
    }

    public function verifyAuth(Request $request) {

        $user = auth()->user();

        $user['permisos'] = $this->permisosApp($user,$request->header('App'));
        $user['menu'] = $this->menu($user);

        $perfil = $user->perfil->nombre;
        unset($user->perfil);
        $user['perfil'] = $perfil;

        $user->makeHidden('perfil_id');

        return response(base64_encode($user->load('dependencia')));

    }

    public function permisosApp($user, $app) {

        $user = $user;
        
        if($user->perfil) {
            $permisos = [];            
            foreach ( $user->perfil->rol->permisos as $permiso ) {
                if( $permiso->app === $app ) {
                    $permisos[] = $permiso->nombre;
                }
            }
        }

        return $permisos;
    }

    public function menu($user) {


        if($user->perfil->menu->paginas) {
            
            $paginas = $user->perfil->menu->paginas->load('padre');
            $grupoPaginas = $paginas->groupBy('pagina_id');
            
            $menu = collect();
            $subMenu = collect();

            foreach ($grupoPaginas as $grupo) {
                foreach ($grupo as $hijo) {
                    if($hijo->padre) {
                        $hijo->padre->subMenu = collect();
                        $menu->push($hijo->padre);
                    } else {
                        $menu->push($hijo);
                    }

                    unset($hijo->padre,$hijo->pivot);
                    $subMenu->push($hijo);
                }
            }

            $menu = $menu->unique('id');

            $menu->each(function ($padre) use ($subMenu) {
                $padre->subMenu = $subMenu->where('pagina_id', $padre->id)->sortBy('orden')->values();
            });

        }

        return $menu->sortBy('orden')->values()->all();

    }

    public function logout() {
        Auth::logout();
        $cookie = Cookie::forget(base64_encode('access_token'));
        return response(['message' => 'Sesión cerrada exitosamente'])
                         ->withCookie($cookie);
    }
}
