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
        $this->secretKey = $_ENV['JWT_SECRET_KEY'];
        $this->issuer    = $_ENV['JWT_ISSUER'];
        $this->expiration = (int) $_ENV['JWT_EXPIRATION'];
    }

    /**
     * Genera un token JWT para un usuario autenticado
     * 
     * @param string $usuario Nombre del usuario autenticado
     * @return string Token JWT generado
     */
    public function generarToken(string $usuario): string
    {
        $payload = [
            'iss' => $this->issuer,
            'iat' => time(),
            'exp' => time() + $this->expiration,
            'sub' => $usuario
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    /**
     * Valida el token recibido en el header Authorization
     * 
     * @return object|null Retorna el payload decodificado si es valido, o null si no lo es
     */
    public function validarToken(): ?object
    {
        $headers = $this->obtenerHeaders();

        if (!isset($headers['Authorization'])) {
            return null;
        }

        $authHeader = $headers['Authorization'];
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return null;
        }

        $jwt = $matches[1];

        try {
            return JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
        } catch (ExpiredException | SignatureInvalidException | \Exception $e) {
            return null;
        }
    }

    /**
     * Obtiene los headers HTTP de forma compatible con diferentes servidores
     * 
     * @return array Arreglo con los headers HTTP
     */
    private function obtenerHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}