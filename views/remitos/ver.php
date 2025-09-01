<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/RemitoController.php';
include_once __DIR__ . '/../../includes/header.php';

$controller = new RemitoController($conn);

if (!isset($_GET['id'])) {
    die("ID inválido");
}
$id = $_GET['id'];

$remito = $controller->obtener($id);
if (!$remito) {
    die("Remito no encontrado");
}
?>

<div class="container mt-4">
  <h2>📄 Detalle del Remito</h2>

  <div class="card p-4 mb-4">
    <p><strong>Número:</strong> <?= htmlspecialchars($remito['numero']) ?></p>
    <p><strong>Fecha:</strong> <?= htmlspecialchars($remito['fecha']) ?></p>
    <p><strong>Tipo:</strong> <?= htmlspecialchars($remito['tipo']) ?></p>
    <p><strong>Usuario:</strong> <?= htmlspecialchars($remito['usuario']) ?></p>
    <p><strong>Observaciones:</strong> <?= htmlspecialchars($remito['observaciones']) ?></p>
  </div>

  <h4>📦 Productos</h4>
  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th>Producto</th>
          <th>Cantidad</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($remito['detalles'] as $d): ?>
          <tr>
            <td><?= htmlspecialchars($d['producto']) ?></td>
            <td><?= htmlspecialchars($d['cantidad']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <a href="listar.php" class="btn btn-secondary">↩️ Volver</a>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
