<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ProductoController.php';

$controller = new ProductoController($conn);
$categoria_id = $_GET['categoria_id'] ?? 0;
$atributos = $controller->listarAtributosPorCategoria($categoria_id);

header('Content-Type: application/json');
echo json_encode($atributos);
