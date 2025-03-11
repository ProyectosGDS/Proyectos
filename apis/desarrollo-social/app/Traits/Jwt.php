<?php

namespace App\Traits;

use App\Jwt\JsonWebToken;

trait Jwt {

    public function createToken(array $payloads, string $aud) {
        $jwt = new JsonWebToken ();
        return $jwt->createJWT($payloads, $aud);
    }
}