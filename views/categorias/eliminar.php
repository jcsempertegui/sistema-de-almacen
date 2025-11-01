<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/CategoriaController.php';

if ($_SESSION['rol'] != 'admin') die("Acceso denegado");

$controller = new CategoriaController($conn);

$id = $_GET['id'] ?? 0;
if (!$id) {
    header("Location: listar.php?error=ID de categoría no especificado");
    exit;
}

$resultado = $controller->eliminar($id);

if ($resultado === true) {
    header("Location: listar.php?msg=Categoría eliminada correctamente");
} elseif ($resultado === "atributos") {
    header("Location: listar.php?error=No se puede eliminar. La categoría tiene atributos asociados.");
} elseif ($resultado === "productos") {
    header("Location: listar.php?error=No se puede eliminar. La categoría tiene productos asociados.");
} else {
    header("Location: listar.php?error=Error al eliminar la categoría.");
}
exit;
