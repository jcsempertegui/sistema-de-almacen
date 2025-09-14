<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Entrega.php';
include_once __DIR__ . '/../../includes/header.php';

$entregaModel = new Entrega($conn);
$entregas = $entregaModel->listar();
?>
<div class="container mt-4">
  <h2>📦 Reporte de Entregas</h2>

  <table class="table table-bordered table-striped" id="tablaEntregas">
    <thead>
      <tr>
        <th>#</th>
        <th>Fecha</th>
        <th>Trabajador</th>
        <th>Registrado por</th>
        <th>Detalles</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($entregas as $e): ?>
        <tr>
          <td><?= $e['id'] ?></td>
          <td><?= $e['fecha'] ?></td>
          <td><?= $e['trabajador'] ?></td>
          <td><?= $e['registrado_por'] ?></td>
          <td>
            <a href="../entregas/ver.php?id=<?= $e['id'] ?>" class="btn btn-info btn-sm">🔍 Ver</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
$(document).ready(function() {
  $('#tablaEntregas').DataTable({
    responsive: true,
    dom: 'Bfrtip',
    buttons: ['excel', 'pdf', 'print']
  });
});
</script>
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
