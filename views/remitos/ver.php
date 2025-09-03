<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/RemitoController.php';
include_once __DIR__ . '/../../includes/header.php';

$controller = new RemitoController($conn);
$id = $_GET['id'] ?? null;
$remito = $controller->obtener($id);
?>

<div class="container mt-4">
  <h2>👁 Detalle Remito #<?= $remito['numero'] ?></h2>

  <p><strong>Tipo:</strong> <?= htmlspecialchars($remito['tipo_remito_id']) ?></p>
  <p><strong>Señores:</strong> <?= htmlspecialchars($remito['señores']) ?></p>
  <p><strong>Atención:</strong> <?= htmlspecialchars($remito['atencion']) ?></p>
  <p><strong>Contrato:</strong> <?= htmlspecialchars($remito['contrato']) ?></p>
  <p><strong>Campo:</strong> <?= htmlspecialchars($remito['campo']) ?></p>
  <p><strong>Orden:</strong> <?= htmlspecialchars($remito['orden']) ?></p>
  <p><strong>Observaciones:</strong> <?= htmlspecialchars($remito['observaciones']) ?></p>
  <p><strong>Despachado:</strong> <?= htmlspecialchars($remito['despachado']) ?></p>
  <p><strong>Transportado:</strong> <?= htmlspecialchars($remito['transportado']) ?></p>
  <p><strong>Placa:</strong> <?= htmlspecialchars($remito['placa']) ?></p>
  <p><strong>Recibido:</strong> <?= htmlspecialchars($remito['recibido']) ?></p>

  <h5>📦 Productos</h5>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Producto</th>
        <th>Atributos</th>
        <th>Cantidad</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($remito['detalles'] as $d): ?>
        <tr>
          <td><?= htmlspecialchars($d['nombre']) ?></td>
          <td><?= htmlspecialchars($d['atributos']) ?></td>
          <td><?= htmlspecialchars($d['cantidad']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <a href="listar.php" class="btn btn-secondary">↩️ Volver</a>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
