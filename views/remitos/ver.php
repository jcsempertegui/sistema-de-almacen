<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/RemitoController.php';
include_once __DIR__ . '/../../includes/header.php';

$controller = new RemitoController($conn);
$id = $_GET['id'] ?? 0;
$remito = $controller->obtener($id);
?>

<div class="card shadow p-4">
  <h3>📑 Remito #<?= htmlspecialchars($remito['numero']) ?></h3>
  <p><b>Tipo:</b> <?= htmlspecialchars($remito['tipo_nombre']) ?></p>
  <p><b>Usuario:</b> <?= htmlspecialchars($remito['usuario_nombre']) ?></p>
  <p><b>Fecha:</b> <?= htmlspecialchars($remito['fecha']) ?></p>
  <p><b>Señores:</b> <?= htmlspecialchars($remito['señores']) ?></p>
  <p><b>Atención:</b> <?= htmlspecialchars($remito['atencion']) ?></p>
  <p><b>Contrato:</b> <?= htmlspecialchars($remito['contrato']) ?></p>
  <p><b>Campo:</b> <?= htmlspecialchars($remito['campo']) ?></p>
  <p><b>Orden:</b> <?= htmlspecialchars($remito['orden']) ?></p>
  <p><b>Observaciones:</b> <?= htmlspecialchars($remito['observaciones']) ?></p>
  <p><b>Despachado:</b> <?= htmlspecialchars($remito['despachado']) ?></p>
  <p><b>Transportado:</b> <?= htmlspecialchars($remito['transportado']) ?></p>
  <p><b>Placa:</b> <?= htmlspecialchars($remito['placa']) ?></p>
  <p><b>Recibido:</b> <?= htmlspecialchars($remito['recibido']) ?></p>

  <h4 class="mt-4">🛒 Detalle de Productos</h4>
  <table class="table table-bordered">
    <thead class="table-light">
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
          <td><?= htmlspecialchars($d['atributos'] ?? '-') ?></td>
          <td><?= $d['cantidad'] ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <a href="listar.php" class="btn btn-secondary">↩ Volver</a>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
