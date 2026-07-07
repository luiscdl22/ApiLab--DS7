<?php

use Deleo\JwtCrud\Database;

$pdo = Database::getConnection();
$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {

    // ============================
    // GET - Consultar productos
    // ============================
    case 'GET':
        if (isset($_GET['codigo'])) {
            // GET de un producto específico
            $stmt = $pdo->prepare("SELECT * FROM productos WHERE codigo = ?");
            $stmt->execute([$_GET['codigo']]);
            $producto = $stmt->fetch();

            if ($producto) {
                http_response_code(200);
                echo json_encode($producto);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Producto no encontrado']);
            }
        } else {
            // GET de todos los productos
            $stmt = $pdo->query("SELECT * FROM productos");
            $productos = $stmt->fetchAll();

            http_response_code(200);
            echo json_encode($productos);
        }
        break;

    // ============================
    // POST - Crear producto
    // ============================
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);

        // Validación básica de campos requeridos
        if (!isset($data['codigo'], $data['producto'], $data['precio'], $data['cantidad'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan campos requeridos: codigo, producto, precio, cantidad']);
            exit;
        }

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO productos (codigo, producto, precio, cantidad) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([
                $data['codigo'],
                $data['producto'],
                $data['precio'],
                $data['cantidad']
            ]);

            http_response_code(201); // 201 = recurso creado
            echo json_encode([
                'mensaje' => 'Producto creado correctamente',
                'id' => $pdo->lastInsertId()
            ]);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear el producto', 'detalle' => $e->getMessage()]);
        }
        break;

    // ============================
    // PUT - Actualizar producto
    // ============================
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['codigo'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Debe indicar el campo "codigo" del producto a actualizar']);
            exit;
        }

        // Verificamos que el producto exista antes de actualizar
        $stmt = $pdo->prepare("SELECT * FROM productos WHERE codigo = ?");
        $stmt->execute([$data['codigo']]);
        $producto = $stmt->fetch();

        if (!$producto) {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado']);
            exit;
        }

        try {
            $stmt = $pdo->prepare(
                "UPDATE productos SET producto = ?, precio = ?, cantidad = ? WHERE codigo = ?"
            );
            $stmt->execute([
                $data['producto'] ?? $producto['producto'],
                $data['precio'] ?? $producto['precio'],
                $data['cantidad'] ?? $producto['cantidad'],
                $data['codigo']
            ]);

            http_response_code(200);
            echo json_encode(['mensaje' => 'Producto actualizado correctamente']);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar', 'detalle' => $e->getMessage()]);
        }
        break;

    // ============================
    // DELETE - Eliminar producto
    // ============================
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['codigo'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Debe indicar el campo "codigo" del producto a eliminar']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM productos WHERE codigo = ?");
        $stmt->execute([$data['codigo']]);
        $producto = $stmt->fetch();

        if (!$producto) {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM productos WHERE codigo = ?");
        $stmt->execute([$data['codigo']]);

        http_response_code(200);
        echo json_encode(['mensaje' => 'Producto eliminado correctamente']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}