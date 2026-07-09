<?php
/**
 * Front Controller - Punto de entrada unico para la API
 * 
 * Este archivo actua como controlador frontal, validando el token JWT
 * antes de derivar las peticiones al controlador correspondiente
 */

require __DIR__ . '/vendor/autoload.php';

use Deleo\JwtCrud\AuthService;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

header('Content-Type: application/json');

$auth = new AuthService();
$decoded = $auth->validarToken();

if (!$decoded) {
    http_response_code(401);
    echo json_encode(['error' => 'Token invalido o ausente']);
    exit;
}

$metodo = $_SERVER['REQUEST_METHOD'];

$metodosPermitidos = ['GET', 'POST', 'PUT', 'DELETE'];
if (!in_array($metodo, $metodosPermitidos)) {
    http_response_code(405);
    echo json_encode(['error' => 'Metodo no permitido']);
    exit;
}

require __DIR__ . '/api/products.php';