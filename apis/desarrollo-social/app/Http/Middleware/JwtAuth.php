<?php

namespace App\Http\Middleware;

use App\Jwt\JsonWebToken;
use App\Models\adm_gds\usuarios;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class JwtAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next): Response {
        $accessToken = $request->bearerToken();
        
        // Si no se encuentra ningún token, denegar el acceso
        if (!$accessToken) {
            return response('Unauthorized', 401);
        }

        try {

            $jwt = new JsonWebToken();
            $jwt->verifyJWT($accessToken); // Validar firma y claims
            $payload = $jwt->decodeJWT($accessToken);
            $user = usuarios::find($payload['sub']);

            if (!$user) {
                return response('Unauthorized', 401);
            }

            Auth::setUser($user);

        } catch (\Throwable $e) {
            return response('Unauthorized: ' . $e->getMessage(), 401);
        }

        return $next($request);
    }
}
