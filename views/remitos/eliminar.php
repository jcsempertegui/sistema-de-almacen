<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/RemitoController.php';

if ($_SESSION['rol'] != 'admin') {
    die("Acceso denegado");
}

$controller = new RemitoController($conn);
$id = $_GET['id'] ?? null;

if ($id) {
    $controller->eliminar($id);
    header("Location: listar.php?msg=Remito eliminado correctamente");
    exit;
}
