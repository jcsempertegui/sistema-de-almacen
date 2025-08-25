<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ProductoController.php';

if ($_SESSION['rol'] != 'admin') die("Acceso denegado");

$controller = new ProductoController($conn);

$id = $_GET['id'] ?? 0;
if ($id) {
    $controller->eliminar($id);
    header("Location: listar.php?msg=Producto eliminado correctamente");
    exit;
} else {
    header("Location: listar.php?msg=Error: producto no encontrado");
    exit;
}
