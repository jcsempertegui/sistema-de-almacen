<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Remito.php';
include_once __DIR__ . '/../../includes/header.php';

$remitoModel = new Remito($conn);
$salidas = $remitoModel->listar('', '');
?>
<div class="container mt-4">
  <h2>📤 Reporte de Salidas</h2>

  <table class="table table-bordered table-striped" id="tablaSalidas">
    <thead>
      <tr>
        <th>#</th>
        <th>Fecha</th>
        <th>Número</th>
        <th>Registrado por</th>
        <th>Detalles</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($salidas as $s): ?>
        <?php if ($s['tipo'] === 'Egreso'): ?>
        <tr>
          <td><?= $s['id'] ?></td>
          <td><?= $s['fecha'] ?></td>
          <td><?= $s['numero'] ?></td>
          <td><?= $s['registrado_por'] ?></td>
          <td>
            <a href="../remitos/ver.php?id=<?= $s['id'] ?>" class="btn btn-info btn-sm">🔍 Ver</a>
          </td>
        </tr>
        <?php endif; ?>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
$(document).ready(function() {
  $('#tablaSalidas').DataTable({
    responsive: true,
    dom: 'Bfrtip',
    buttons: ['excel', 'pdf', 'print']
  });
});
</script>
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
