<?php
require __DIR__ . '/vendor/autoload.php';

use Deleo\JwtCrud\AuthService;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

header('Content-Type: application/json');

$auth = new AuthService();
$decoded = $auth->validarToken();

if (!$decoded) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido o ausente']);
    exit;
}

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':
    case 'POST':
    case 'PUT':
    case 'DELETE':
        require __DIR__ . '/api/products.php';
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}