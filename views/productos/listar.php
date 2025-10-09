<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ProductoController.php';
include_once __DIR__ . '/../../includes/header.php';

$controller = new ProductoController($conn);
$productos = $controller->listar();
?>
<div class="d-flex justify-content-between mb-3">
<h2>📦 Productos</h2>
<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>

  <?php if ($_SESSION['rol'] == 'admin'): ?>
    <a href="crear.php" class="btn btn-success">➕ Nueva Producto</a>
  <?php endif; ?>

</div>

<div class="container mt-4">


  <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Categoría</th>
          <th>Unidad</th>
          <th>Stock</th>
          <th>Atributos</th>
          <?php if ($_SESSION['rol'] == 'admin'): ?>
            <th>Acciones</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($productos)): ?>
          <?php foreach ($productos as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['id']) ?></td>
              <td><?= htmlspecialchars($p['nombre']) ?></td>
              <td><?= htmlspecialchars($p['categoria']) ?></td>
              <td><?= htmlspecialchars($p['unidad']) ?></td>
              <td><?= htmlspecialchars($p['stock']) ?></td>
              <td><?= htmlspecialchars($p['atributos']) ?></td>
              <?php if ($_SESSION['rol'] == 'admin'): ?>
                <td>
                  <a href="editar.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">✏️ Editar</a>
                  <a href="eliminar.php?id=<?= $p['id'] ?>" class="btn btn-danger btn-sm"
                     onclick="return confirm('¿Eliminar producto?')">🗑 Eliminar</a>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center">No hay productos registrados</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
