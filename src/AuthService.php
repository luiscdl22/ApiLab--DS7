<?php

namespace Deleo\JwtCrud;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class AuthService
{
    private string $secretKey;
    private string $issuer;
    private int $expiration;

    public function __construct()
    {
        // Cargamos las variables de entorno definidas en .env
        $this->secretKey = $_ENV['JWT_SECRET_KEY'];
        $this->issuer    = $_ENV['JWT_ISSUER'];
        $this->expiration = (int) $_ENV['JWT_EXPIRATION'];
    }

    /**
     * Genera un token JWT para un usuario autenticado.
     */
    public function generarToken(string $usuario): string
    {
        $payload = [
            'iss' => $this->issuer,            // Quién emite el token
            'iat' => time(),                   // Fecha de emisión
            'exp' => time() + $this->expiration, // Fecha de expiración
            'sub' => $usuario                  // Usuario autenticado
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    /**
     * Valida el token recibido en el header Authorization.
     * Retorna el payload decodificado si es válido, o null si no lo es.
     */
    public function validarToken(): ?object
{
    $headers = $this->obtenerHeaders();

    if (!isset($headers['Authorization'])) {
        // DEBUG
        echo json_encode(['debug' => 'no isset Authorization', 'headers' => $headers]);
        exit;
    }

    $authHeader = $headers['Authorization'];
    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        echo json_encode(['debug' => 'no match regex', 'authHeader' => $authHeader]);
        exit;
    }

    $jwt = $matches[1];

    try {
        return JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
    } catch (ExpiredException | SignatureInvalidException | \Exception $e) {
        echo json_encode(['debug' => 'exception', 'mensaje' => $e->getMessage()]);
        exit;
    }
}

    /**
     * Obtiene los headers HTTP de forma compatible con WAMP/Apache.
     */
    private function obtenerHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        // Fallback para servidores donde getallheaders() no existe
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}