<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if ($_SESSION['rol'] !== 'admin') { die('Acceso denegado'); }

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/RemitoController.php';

$id = (int)($_GET['id'] ?? 0);
$controller = new RemitoController($conn);

try {
    $controller->eliminar($id);
    header("Location: listar.php?msg=Remito eliminado");
} catch (Throwable $e) {
    header("Location: listar.php?msg=Error al eliminar: ".urlencode($e->getMessage()));
}
exit;
