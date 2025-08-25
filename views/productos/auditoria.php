<?php
require_once __DIR__ . '/../../config/db.php';

if ($_SESSION['rol'] != 'admin') {
    die("Acceso denegado");
}

// Consultamos los registros de auditoría
$sql = "SELECT a.id, a.producto_id, p.nombre as producto, 
               u.usuario as usuario, a.campo, 
               a.valor_anterior, a.valor_nuevo, a.fecha
        FROM auditoria_producto a
        JOIN producto p ON a.producto_id = p.id
        JOIN usuario u ON a.usuario_id = u.id
        ORDER BY a.fecha DESC";

$stmt = $conn->query($sql);
$auditorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once __DIR__ . '/../../includes/header.php';
?>

<h2 class="mb-4">📜 Auditoría de Productos</h2>

<div class="table-responsive">
  <table class="table table-striped table-bordered align-middle">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Producto</th>
        <th>Campo</th>
        <th>Valor Anterior</th>
        <th>Valor Nuevo</th>
        <th>Usuario</th>
        <th>Fecha</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($auditorias) > 0): ?>
        <?php foreach ($auditorias as $a): ?>
          <tr>
            <td><?= $a['id'] ?></td>
            <td><?= htmlspecialchars($a['producto']) ?> (#<?= $a['producto_id'] ?>)</td>
            <td><?= htmlspecialchars($a['campo']) ?></td>
            <td><?= htmlspecialchars($a['valor_anterior']) ?></td>
            <td><?= htmlspecialchars($a['valor_nuevo']) ?></td>
            <td><?= htmlspecialchars($a['usuario']) ?></td>
            <td><?= $a['fecha'] ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="7" class="text-center">No hay registros de auditoría</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<a href="listar.php" class="btn btn-secondary">⬅️ Volver</a>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
