<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/RemitoController.php';
include_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$controller = new RemitoController($conn);
$r = $controller->obtener($id);
?>
<div class="container mt-3">
  <h3>Remito #<?= $r['id'] ?> — <?= htmlspecialchars($r['tipo']) ?></h3>
  <div class="row">
    <div class="col-md-6">
      <table class="table table-sm">
        <tr><th>Fecha</th><td><?= htmlspecialchars($r['fecha']) ?></td></tr>
        <tr><th>N° Remito</th><td><?= htmlspecialchars($r['numero_remito']) ?></td></tr>
        <tr><th>Campo</th><td><?= htmlspecialchars($r['campo']) ?></td></tr>
        <tr><th>Orden</th><td><?= htmlspecialchars($r['orden']) ?></td></tr>
        <tr><th>Observaciones</th><td><?= nl2br(htmlspecialchars($r['observaciones'])) ?></td></tr>
      </table>
    </div>
    <div class="col-md-6">
      <table class="table table-sm">
        <tr><th>Señores</th><td><?= htmlspecialchars($r['señores']) ?></td></tr>
        <tr><th>Atención</th><td><?= htmlspecialchars($r['atencion']) ?></td></tr>
        <tr><th>Contrato</th><td><?= htmlspecialchars($r['contrato']) ?></td></tr>
        <tr><th>Despachado</th><td><?= htmlspecialchars($r['despachado']) ?></td></tr>
        <tr><th>Transportado</th><td><?= htmlspecialchars($r['transportado']) ?></td></tr>
        <tr><th>Placa</th><td><?= htmlspecialchars($r['placa']) ?></td></tr>
        <tr><th>Recibido</th><td><?= htmlspecialchars($r['recibido']) ?></td></tr>
      </table>
    </div>
  </div>

  <h5>Detalle</h5>
  <div class="table-responsive">
    <table class="table table-bordered">
      <thead class="table-light">
        <tr><th>#</th><th>Producto</th><th>Unidad</th><th>Cantidad</th></tr>
      </thead>
      <tbody>
      <?php foreach ($r['detalles'] as $i => $d): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= htmlspecialchars($d['producto']) ?></td>
          <td><?= htmlspecialchars($d['unidad']) ?></td>
          <td><?= (int)$d['cantidad'] ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($r['detalles'])): ?>
        <tr><td colspan="4" class="text-center">Sin detalles</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <a class="btn btn-secondary" href="listar.php">↩ Volver</a>
  <?php if ($_SESSION['rol'] === 'admin'): ?>
    <a class="btn btn-warning" href="editar.php?id=<?= $r['id'] ?>">✏️ Editar</a>
    <a class="btn btn-danger" href="eliminar.php?id=<?= $r['id'] ?>" onclick="return confirm('¿Eliminar remito y revertir stock?')">🗑 Eliminar</a>
  <?php endif; ?>
</div>
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
