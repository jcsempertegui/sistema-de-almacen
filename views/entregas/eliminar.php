<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/EntregaController.php';

if ($_SESSION['rol'] != 'admin') die("Acceso denegado");

$controller = new EntregaController($conn);
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    $controller->eliminar($id);
    header("Location: listar.php?msg=Entrega eliminada correctamente");
    exit;
} else {
    header("Location: listar.php?msg=Error al eliminar entrega");
    exit;
}
