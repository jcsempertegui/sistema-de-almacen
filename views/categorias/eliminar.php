<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/CategoriaController.php';
include_once __DIR__ . '/../../includes/header.php';

if ($_SESSION['rol'] != 'admin') die("Acceso denegado");

if (!isset($_GET['id'])) {
    die("ID no especificado");
}

$id = intval($_GET['id']);
$controller = new CategoriaController($conn);
$categoria = $controller->obtener($id);

if (!$categoria) {
    die("Categoría no encontrada");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
}
?>

<div class="container mt-4">
  <h2>🗑 Eliminar Categoría</h2>
  <div class="alert alert-danger">
    ¿Está seguro de que desea eliminar la categoría <strong><?= htmlspecialchars($categoria['nombre']) ?></strong>?  
    Esta acción no se puede deshacer.
  </div>

  <form method="POST">
    <button type="submit" class="btn btn-danger">Sí, eliminar</button>
    <a href="listar.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
