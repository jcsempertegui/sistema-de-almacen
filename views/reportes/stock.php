<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Producto.php';
include_once __DIR__ . '/../../includes/header.php';

$productoModel = new Producto($conn);
$productos = $productoModel->listar();
?>
<div class="container mt-4">
  <h2>📊 Reporte de Stock</h2>

  <table class="table table-bordered table-striped" id="tablaStock">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Categoría</th>
        <th>Unidad</th>
        <th>Stock</th>
        <th>Atributos</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($productos as $p): ?>
        <tr>
          <td><?= $p['id'] ?></td>
          <td><?= $p['nombre'] ?></td>
          <td><?= $p['categoria'] ?></td>
          <td><?= $p['unidad'] ?></td>
          <td><?= $p['stock'] ?></td>
          <td><?= $p['atributos'] ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
$(document).ready(function() {
  $('#tablaStock').DataTable({
    responsive: true,
    dom: 'Bfrtip',
    buttons: ['excel', 'pdf', 'print']
  });
});
</script>
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
