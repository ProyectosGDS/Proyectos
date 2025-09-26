<?php

namespace App\Traits;

use App\Services\Jwt\JsonWebToken;

trait Jwt {

    public function createToken(array $payloads, string $aud) {
        $jwt = new JsonWebToken ();
        return $jwt->createJWT($payloads, $aud);
    }
}