<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/EntregaController.php';
include_once __DIR__ . '/../../includes/header.php';

$controller = new EntregaController($conn);
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$entrega = $controller->obtener($id);
?>

<div class="container mt-4">
  <h2>👁 Detalle Entrega #<?= htmlspecialchars($entrega['id'] ?? '') ?></h2>

  <p><strong>Fecha:</strong> <?= htmlspecialchars($entrega['fecha'] ?? '') ?></p>
  <p><strong>Trabajador:</strong> <?= htmlspecialchars($entrega['trabajador_nombre'] ?? '') ?></p>
  <p><strong>Inspector:</strong> <?= htmlspecialchars($entrega['inspector'] ?? '') ?></p>
  <p><strong>Campo:</strong> <?= htmlspecialchars($entrega['campo'] ?? '') ?></p>

  <h5 class="mt-3">📦 Productos</h5>
  <table class="table table-bordered">
    <thead>
      <tr><th>Producto</th><th>Atributos</th><th>Cantidad</th><th>Motivo</th></tr>
    </thead>
    <tbody>
      <?php foreach ($entrega['detalles'] as $d): ?>
        <tr>
          <td><?= htmlspecialchars($d['nombre']) ?></td>
          <td><?= htmlspecialchars($d['atributos']) ?></td>
          <td><?= htmlspecialchars($d['cantidad']) ?></td>
          <td><?= htmlspecialchars($d['motivo']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <a href="listar.php" class="btn btn-secondary">↩ Volver</a>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>