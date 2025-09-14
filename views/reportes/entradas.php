<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Remito.php';
include_once __DIR__ . '/../../includes/header.php';

$remitoModel = new Remito($conn);
$entradas = $remitoModel->listar('', ''); // sin filtros por ahora
?>
<div class="container mt-4">
  <h2>📥 Reporte de Entradas</h2>

  <table class="table table-bordered table-striped" id="tablaEntradas">
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
      <?php foreach ($entradas as $e): ?>
        <?php if ($e['tipo'] === 'Ingreso'): ?>
        <tr>
          <td><?= $e['id'] ?></td>
          <td><?= $e['fecha'] ?></td>
          <td><?= $e['numero'] ?></td>
          <td><?= $e['registrado_por'] ?></td>
          <td>
            <a href="../remitos/ver.php?id=<?= $e['id'] ?>" class="btn btn-info btn-sm">🔍 Ver</a>
          </td>
        </tr>
        <?php endif; ?>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
$(document).ready(function() {
  $('#tablaEntradas').DataTable({
    responsive: true,
    dom: 'Bfrtip',
    buttons: ['excel', 'pdf', 'print']
  });
});
</script>
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
