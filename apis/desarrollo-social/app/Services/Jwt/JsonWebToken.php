<?php

namespace App\Services\Jwt;

use Exception;

class JsonWebToken
{
    private string $pathPrivateKey;
    private string $pathPublicKey;

    // Crear la clave privada y necesita contraseña para encriptarla
    // openssl genpkey -algorithm RSA -out private_key.pem -aes256 

    // Extrae la clave publica de la privada
    // openssl rsa -in private_key.pem -pubout -out public_key.pem

    public function __construct() {
        $this->pathPrivateKey = 'file://' . storage_path('keys') . '/private_key.pem';
        $this->pathPublicKey = 'file://' . storage_path('keys') . '/public_key.pem';
    }

    public function createJWT(array $payloads, string $aud): string {
        $privateKey = openssl_pkey_get_private($this->pathPrivateKey, config("jwt.secret"));

        if (!$privateKey) {
            throw new \Exception("No se pudo cargar la clave privada");
        }

        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $payload = [
            'iss' => env('APP_URL'),
            'aud' => $aud,
            'iat' => time(),
            'exp' => time() + (intval(config("jwt.expired_token")) * 3600), //EXPIRACION DE EN HORAS
            'sub' => $payloads['sub'] ?? null,
        ];
        
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));
        $signature = '';

        if (!openssl_sign("$base64UrlHeader.$base64UrlPayload", $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new \Error("No se pudo firmar el JWT");
        }
        
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    public function verifyJWT(string $jwt): bool {

        try {

            if (substr_count($jwt, '.') !== 2) {
                throw new \Exception($jwt);
            }

            list($header, $payload, $signature) = explode('.', $jwt);

            $decodedPayload = $this->decodeJWT($jwt);

            // Validación de claims antes de la firma
            $receivers = config('jwt.receivers');
            $currentTimestamp = time();
        
            if (isset($decodedPayload['aud']) && !in_array($decodedPayload['aud'], $receivers)) {
                throw new \Exception('Audience inválido', 401);
            }
        
            if (isset($decodedPayload['exp']) && $currentTimestamp > $decodedPayload['exp']) {
                throw new \Exception('El token ha expirado.', 401);
            }
        
            $publicKey = openssl_pkey_get_public(file_get_contents($this->pathPublicKey));
            
            if (!$publicKey) {
                throw new \Exception("No se pudo cargar la clave pública");
            }
        
            $base64UrlSignature = str_replace(['-', '_'], ['+', '/'], $signature);
            $signature = base64_decode($base64UrlSignature);
        
            // Verificación de la firma después de las claims
            $isValid = openssl_verify("$header.$payload", $signature, $publicKey, OPENSSL_ALGO_SHA256);
            
            if ($isValid !== 1) {
                throw new \Exception('Firma de token inválida', 401);
            }
            
            return true;
        } catch (\Exception $e) {
            throw new Exception('Invalid JWT: ' . $e->getMessage());
        }

       
    }

    public function decodeJWT(string $jwt): array {
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $jwt);
        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
        
        return $payload;
    }
    
    private function base64UrlEncode(string $data): string {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private function base64UrlDecode(string $data): string {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}