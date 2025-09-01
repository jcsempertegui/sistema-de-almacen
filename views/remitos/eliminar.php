<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/RemitoController.php';

if ($_SESSION['rol'] != 'admin') {
    die("Acceso denegado");
}

if (!isset($_GET['id'])) {
    die("ID inválido");
}

$id = $_GET['id'];
$controller = new RemitoController($conn);

if ($controller->eliminar($id)) {
    header("Location: listar.php?msg=Remito eliminado correctamente");
} else {
    header("Location: listar.php?msg=Error al eliminar el remito");
}
exit;
