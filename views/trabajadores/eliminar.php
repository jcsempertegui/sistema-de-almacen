<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/TrabajadorController.php';

$controller = new TrabajadorController($conn);
$id = $_GET['id'] ?? null;

if ($id) {
    $controller->eliminar($id);
    header("Location: listar.php?msg=Trabajador eliminado correctamente");
    exit;
} else {
    header("Location: listar.php?msg=Error al eliminar trabajador");
    exit;
}
