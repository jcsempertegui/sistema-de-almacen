<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/CategoriaController.php';
include_once __DIR__ . '/../../includes/header.php';

$controller = new CategoriaController($conn);
$categorias = $controller->listar();
?>

<div class="d-flex justify-content-between mb-3">
  <h2>📂 Categorías</h2>
  <?php if (isset($_GET['msg'])): ?>
  <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['error'])): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
  <?php endif; ?>
  <a href="crear.php" class="btn btn-success">➕ Nueva Categoría</a>
</div>

<div class="container mt-4">
  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <?php if ($_SESSION['rol'] == 'admin'): ?><th>Acciones</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if (count($categorias) > 0): ?>
          <?php foreach ($categorias as $c): ?>
            <tr>
              <td><?= $c['id'] ?></td>
              <td><?= htmlspecialchars($c['nombre']) ?></td>
              <?php if ($_SESSION['rol'] == 'admin'): ?>
              <td>
                <a href="editar.php?id=<?= $c['id'] ?>" class="btn btn-warning btn-sm">✏️ Editar</a>
                <a href="eliminar.php?id=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar categoría?')">🗑️ Eliminar</a>
              </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="3" class="text-center">No hay categorías registradas</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>
    </div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
  setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) {
      alert.classList.remove('show');
      setTimeout(() => alert.remove(), 500);
    }
  }, 4000); // desaparece después de 4 segundos
</script>

