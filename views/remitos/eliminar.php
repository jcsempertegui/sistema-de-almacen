<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/RemitoController.php';

$controller = new RemitoController($conn);
$id = $_GET['id'] ?? 0;

if ($id) {
    try {
        $controller->eliminar($id);
        header("Location: listar.php?msg=Remito eliminado correctamente");
        exit;
    } catch (Exception $ex) {
        die("Error al eliminar: " . $ex->getMessage());
    }
} else {
    header("Location: listar.php?msg=ID inválido");
    exit;
}
