<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/AtributoController.php';
include_once __DIR__ . '/../../includes/header.php';

if ($_SESSION['rol'] != 'admin') die("Acceso denegado");

$controller = new AtributoController($conn);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre' => $_POST['nombre'],
        'categoria_id' => $_POST['categoria_id']
    ];
    $controller->crear($data);
    header("Location: listar.php?msg=Atributo creado correctamente");
    exit;
}

$categorias = $controller->listarCategorias();
?>

<div class="container mt-4">
  <h2>➕ Nuevo Atributo</h2>

  <form method="POST" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre del Atributo</label>
      <input type="text" class="form-control" id="nombre" name="nombre" required>
    </div>

    <div class="mb-3">
      <label for="categoria_id" class="form-label">Categoría</label>
      <select class="form-select" id="categoria_id" name="categoria_id" required>
        <option value="">Seleccione una categoría</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">💾 Guardar</button>
    <a href="listar.php" class="btn btn-secondary">↩️ Cancelar</a>
  </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
