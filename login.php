<?php
require __DIR__ . '/vendor/autoload.php';

use Deleo\JwtCrud\Database;
use Deleo\JwtCrud\AuthService;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$usuario = $data['usuario'] ?? '';
$password = $data['password'] ?? '';

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
$stmt->execute([$usuario]);
$user = $stmt->fetch();

// Aquí está el corazón de la validación segura:
if ($user && password_verify($password, $user['password'])) {
    $auth = new AuthService();
    $token = $auth->generarToken($usuario);

    http_response_code(200);
    echo json_encode(['token' => $token]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Credenciales inválidas']);
}