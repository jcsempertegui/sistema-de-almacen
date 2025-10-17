<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/CategoriaController.php';


if ($_SESSION['rol'] != 'admin') die("Acceso denegado");

$controller = new CategoriaController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = ['nombre' => $_POST['nombre']];
    $controller->crear($data);
    header("Location: listar.php?msg=Categoría creada correctamente");
    exit;
}
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
  <h2>➕ Nueva Categoría</h2>

  <form method="POST" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre de la Categoría</label>
      <input type="text" class="form-control" id="nombre" name="nombre" required>
    </div>

    <button type="submit" class="btn btn-primary">💾 Guardar</button>
    <a href="listar.php" class="btn btn-secondary">↩️ Cancelar</a>
  </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
