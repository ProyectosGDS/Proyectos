<?php

namespace App\Jwt;

class JsonWebToken {

    protected string $newToken;
    protected string $header;
    protected string $payload;

    // Crear la clave privada y necesita contraseña para encriptarla
    // openssl genpkey -algorithm RSA -out private_key.pem -aes256 

    // Extrae la clave publica de la privada
    // openssl rsa -in private_key.pem -pubout -out public_key.pem

    public function createJWT (array $payloads,$aud) : string {

        $pathPrivateKey = 'file://'.storage_path('keys');
        // $pathPrivateKey = 'file:///var/www/html/gds/apis/desarrollo-social/storage/keys/';
        
        $password_encrypt_keys = config("jwt.secret");

        $privateKey = openssl_pkey_get_private($pathPrivateKey.'/private_key.pem', $password_encrypt_keys );
        // $privateKey = openssl_pkey_get_private($pathPrivateKey.'private_key.pem', $password_encrypt_keys );

        if(!$privateKey) {
            throw new \Exception("No se pudo cargar la clave privada");
        }

        $this->header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        
        $payload = [
            'iss' => env('APP_URL'),
            'aud' => $aud,
            'iat' => time(), 
            'exp' => time() + intval(config("jwt.expired_token")) * 3600 //equivale a un minuto, 
        ];

        foreach ($payloads as $key => $value) {
            $payload[$key] = $value;
        }

        $this->payload = json_encode($payload);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($this->header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($this->payload));

        $signature = '';

        if (!openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new \Error("No se pudo firmar el JWT");
        }
        
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        $this->newToken = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        
        return $this->newToken;

    }

    public function verifyJWT($jwt) : bool {

        if(is_null($jwt)){

            return false;
        }

        $publicKey = file_get_contents(storage_path('keys')."/public_key.pem");

        list($header, $payload, $signature) = explode('.', $jwt);
        
        $base64UrlSignature = str_replace(['-', '_'], ['+', '/'], $signature);
        
        $signature = base64_decode($base64UrlSignature);
        
        $isValid = openssl_verify($header . "." . $payload, $signature, $publicKey, OPENSSL_ALGO_SHA256);

        $payload = $this->decodeJWT($jwt);

        $receivers = config('jwt.receivers');

        $currentTimestamp = time();
    
        // if (isset($payload['iss']) && $payload['iss'] !== env('APP_URL')) {
        //     throw new \Error('Issuer inválido.',401);
        // }

        if (isset($payload['aud']) && !in_array($payload['aud'],$receivers)) {
            throw new \Exception('Audience inválido',401);
        }
    
        if (isset($payload['exp']) && $currentTimestamp > $payload['exp']) {
            throw new \Exception('El token ha expirado.',401);
        }
    
        if (isset($payload['iat']) && $currentTimestamp < $payload['iat']) {
            throw new \Exception('El token se emitió en un momento inválido.',401);
        }
    
        return $isValid;
    }

    public function decodeJWT($jwt) : array {

        list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $jwt);
    
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payloadEncoded)), true);

        return $payload;
    }

}