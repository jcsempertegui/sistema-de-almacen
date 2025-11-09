<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ProductoController.php';

// Solo admin puede eliminar
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    die("Acceso denegado");
}

$controller = new ProductoController($conn);

// Validar id en GET
if (!isset($_GET['id'])) {
    header("Location: listar.php?error=" . urlencode("ID no especificado."));
    exit;
}

$id = (int)$_GET['id'];
$producto = $controller->obtener($id);

if (!$producto) {
    header("Location: listar.php?error=" . urlencode("Producto no encontrado."));
    exit;
}


// 🟢 Obtener atributos del producto
$sqlAttr = "
    SELECT GROUP_CONCAT(CONCAT(a.nombre, ': ', ap.valor) SEPARATOR ', ') AS atributos
    FROM atributo_producto ap
    INNER JOIN atributo a ON a.id = ap.atributo_id
    WHERE ap.producto_id = ?
";
$stmt = $conn->prepare($sqlAttr);
$stmt->bind_param("i", $id);
$stmt->execute();
$attr = $stmt->get_result()->fetch_assoc();
$atributos = $attr['atributos'] ?? '';

// Si viene POST -> intentar eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = $controller->eliminar($id);

    if ($resultado === true) {
        header("Location: listar.php?msg=" . urlencode("Producto eliminado correctamente."));
        exit;
    } else {
        // resultado es mensaje de error (string)
        header("Location: listar.php?error=" . urlencode($resultado));
        exit;
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
  <h2>🗑 Eliminar Producto</h2>

  <?php if ($producto): ?>
    <div class="alert alert-danger">
      ¿Está seguro de que desea eliminar el producto <strong><?= htmlspecialchars($producto['nombre']) ?>
      <?php if (!empty($atributos)): ?>
        — <?= htmlspecialchars($atributos) ?></strong>
      <?php endif; ?>  
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
