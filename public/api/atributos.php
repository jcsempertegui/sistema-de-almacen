<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Producto.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $productoModel = new Producto($conn);

    if (!isset($_GET['categoria_id'])) {
        echo json_encode([]);
        exit;
    }

    $categoria_id = (int) $_GET['categoria_id'];
    $atributos = $productoModel->listarAtributosPorCategoria($categoria_id);
    echo json_encode($atributos);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error cargando atributos']);
}
