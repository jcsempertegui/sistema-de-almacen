<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/AtributoController.php';

if ($_SESSION['rol'] != 'admin') die("Acceso denegado");

if (!isset($_GET['id'])) {
    die("ID no especificado");
}

$id = intval($_GET['id']);
$controller = new AtributoController($conn);
$atributo = $controller->obtener($id);

if (!$atributo) {
    die("Atributo no encontrado");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = $controller->eliminar($id);

    if ($resultado === true) {
        header("Location: listar.php?msg=Atributo eliminado correctamente");
    } elseif ($resultado === "en_uso") {
        header("Location: listar.php?error=No se puede eliminar. El atributo está en uso por productos.");
    } else {
        header("Location: listar.php?error=Error al eliminar el atributo.");
    }
    exit;
}
include_once __DIR__ . '/../../includes/header.php';

?>

<div class="container mt-4">
  <h2>🗑 Eliminar Atributo</h2>
  <div class="alert alert-danger">
    ¿Está seguro de que desea eliminar el atributo <strong><?= htmlspecialchars($atributo['nombre']) ?></strong>?  
    Esta acción no se puede deshacer.
  </div>

  <form method="POST">
    <button type="submit" class="btn btn-danger">Sí, eliminar</button>
    <a href="listar.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
