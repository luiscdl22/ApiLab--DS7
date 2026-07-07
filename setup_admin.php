<?php
require __DIR__ . '/vendor/autoload.php';

use Deleo\JwtCrud\Database;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$usuario = 'admin';
$passwordPlano = 'admin123'; // cámbiala si quieres

// Aquí está el corazón del Paso 2:
$hash = password_hash($passwordPlano, PASSWORD_BCRYPT);

$pdo = Database::getConnection();
$stmt = $pdo->prepare("INSERT INTO usuarios (usuario, password) VALUES (?, ?)");
$stmt->execute([$usuario, $hash]);

echo "Usuario admin creado con hash: $hash";