<?php 
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ProductoController.php';

if ($_SESSION['rol'] != 'admin') {
    die("Acceso denegado");
}

if (!isset($_GET['id'])) {
    header("Location: listar.php?error=ID no especificado");
    exit;
}

$id = intval($_GET['id']);
$controller = new ProductoController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->eliminar($id);
    exit;
}

$stmt = $conn->prepare("SELECT nombre FROM producto WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();

include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
  <h2>🗑 Eliminar Producto</h2>

  <?php if ($producto): ?>
    <div class="alert alert-danger">
      ¿Está seguro de que desea eliminar el producto <strong><?= htmlspecialchars($producto['nombre']) ?></strong>?  
      Esta acción no se puede deshacer.
    </div>

    <form method="POST">
      <button type="submit" class="btn btn-danger">Sí, eliminar</button>
      <a href="listar.php" class="btn btn-secondary">Cancelar</a>
    </form>
  <?php else: ?>
    <div class="alert alert-warning">Producto no encontrado.</div>
    <a href="listar.php" class="btn btn-secondary">Volver</a>
  <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
