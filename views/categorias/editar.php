<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/CategoriaController.php';
include_once __DIR__ . '/../../includes/header.php';

if ($_SESSION['rol'] != 'admin') die("Acceso denegado");

if (!isset($_GET['id'])) die("ID no especificado");

$id = intval($_GET['id']);
$controller = new CategoriaController($conn);
$categoria = $controller->obtener($id);

if (!$categoria) die("Categoría no encontrada");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = ['nombre' => $_POST['nombre']];
    $controller->editar($id, $data);
    header("Location: listar.php?msg=Categoría editada correctamente");
    exit;
}
?>

<div class="container mt-4">
  <h2>✏️ Editar Categoría</h2>

  <form method="POST" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre de la Categoría</label>
      <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($categoria['nombre']) ?>" required>
    </div>

    <button type="submit" class="btn btn-success">💾 Guardar Cambios</button>
    <a href="listar.php" class="btn btn-secondary">↩️ Cancelar</a>
  </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
