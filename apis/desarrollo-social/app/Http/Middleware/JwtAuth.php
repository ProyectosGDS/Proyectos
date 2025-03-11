<?php

namespace App\Http\Middleware;

use App\Jwt\JsonWebToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class JwtAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accessToken = Cookie::get(base64_encode('access_token'));

        if(!is_null($accessToken)){
            
            $jwt = new JsonWebToken();

            if(!$jwt->verifyJWT($accessToken)) {
                return response('Unauthorized', 422);
            }

            $payload = $jwt->decodeJWT($accessToken);
            Auth::loginUsingId($payload['sub']);
            return $next($request);
        }

        return response('Unauthorized', 422);
    }
}
