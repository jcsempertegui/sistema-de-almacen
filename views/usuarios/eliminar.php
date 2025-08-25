<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/UsuarioController.php';

if ($_SESSION['rol'] != 'admin') die("Acceso denegado");

$controller = new UsuarioController($conn);
if (isset($_GET['id'])) {
    $controller->eliminar($_GET['id']);
    header("Location: listar.php?msg=Usuario eliminado");
    exit;
}
