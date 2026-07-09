<?php
/**
 * Script de configuracion inicial
 * 
 * Crea el usuario administrador con su contraseña hasheada
 * Ejecutar solo una vez al iniciar el proyecto
 */

require __DIR__ . '/vendor/autoload.php';

use Deleo\JwtCrud\Database;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$usuario = 'admin';
$passwordPlano = 'admin123';

$hash = password_hash($passwordPlano, PASSWORD_BCRYPT);

$pdo = Database::getConnection();
$stmt = $pdo->prepare("INSERT INTO usuarios (usuario, password) VALUES (?, ?)");
$stmt->execute([$usuario, $hash]);

echo "Usuario admin creado con hash: $hash";