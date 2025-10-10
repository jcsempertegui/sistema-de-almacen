<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ReporteController.php';
include_once __DIR__ . '/../../includes/header.php';

$controller = new ReporteController($conn);

// 📅 Filtros
$fechaInicio  = $_GET['fecha_inicio'] ?? '';
$fechaFin     = $_GET['fecha_fin'] ?? '';
$trabajadorId = $_GET['trabajador_id'] ?? '';
$usuarioId    = $_GET['usuario_id'] ?? '';
$productoId   = $_GET['producto_id'] ?? '';

try {
    $entregas = $controller->entregas($fechaInicio, $fechaFin, $trabajadorId, $usuarioId, $productoId);
} catch (Exception $ex) {
    $entregas = [];
    $error = $ex->getMessage();
}

$trabajadores = $controller->listarTrabajadores();
$usuarios     = $controller->listarUsuarios();
$productos    = $controller->listarProductos();
?>

<style>
  .container {
    max-width: 95%;
    margin: auto;
  }
  .filtros-card {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
  }
  .filtros-card .form-label {
    font-weight: 500;
  }
  .table th {
    white-space: nowrap;
  }
  .btn {
    font-weight: 500;
  }
</style>

<div class="container mt-4">
  <h2>📦 Reporte de Entregas a Trabajadores</h2>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- 🔍 FILTROS -->
  <form method="GET" class="card card-body mb-3">
    <div class="row g-3 align-items-end">
      <div class="col-md-2">
        <label class="form-label">Fecha inicio</label>
        <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fechaInicio) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Fecha fin</label>
        <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fechaFin) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Trabajador</label>
        <select name="trabajador_id" class="form-select">
          <option value="">Todos</option>
          <?php foreach ($trabajadores as $t): ?>
            <option value="<?= $t['id'] ?>" <?= ($trabajadorId == $t['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($t['nombre'] . ' ' . $t['apellido_paterno'] . ' ' . $t['apellido_materno']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Usuario</label>
        <select name="usuario_id" class="form-select">
          <option value="">Todos</option>
          <?php foreach ($usuarios as $u): ?>
            <option value="<?= $u['id'] ?>" <?= ($usuarioId == $u['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($u['usuario']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Producto</label>
        <select name="producto_id" class="form-select">
          <option value="">Todos</option>
          <?php foreach ($productos as $p): ?>
            <option value="<?= $p['id'] ?>" <?= ($productoId == $p['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($p['nombre']) ?><?= !empty($p['atributos']) ? ' — ' . htmlspecialchars($p['atributos']) : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary me-2">🔍 Filtrar</button>
        <a href="entregas.php" class="btn btn-secondary me-2">❌ Limpiar</a>
        <button type="button" onclick="imprimirReporte()" class="btn btn-success">🖨 Imprimir</button>
      </div>
    </div>
  </form>

  <!-- 📊 TABLA -->
  <div class="card shadow-sm" id="reporteArea">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-bordered mb-0">
          <thead class="table-dark">
            <tr>
              <th>Fecha</th>
              <th>Trabajador</th>
              <th>Inspector</th>
              <th>Producto</th>
              <th>Cantidad</th>
              <th>Usuario</th>
              <th>Campo</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($entregas)): ?>
            <tr><td colspan="7" class="text-center">No se encontraron resultados</td></tr>
          <?php else: ?>
            <?php foreach ($entregas as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['fecha']) ?></td>
                <td><?= htmlspecialchars($row['trabajador']) ?></td>
                <td><?= htmlspecialchars($row['inspector']) ?></td>
                <td>
                  <?= htmlspecialchars($row['producto']) ?>
                  <?php if (!empty($row['atributos'])): ?>
                    <small class="text-muted"> — <?= htmlspecialchars($row['atributos']) ?></small>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['cantidad']) ?></td>
                <td><?= htmlspecialchars($row['usuario']) ?></td>
                <td><?= htmlspecialchars($row['campo']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- 🖨 IMPRESIÓN -->
<script>
function imprimirReporte() {
  const area = document.getElementById("reporteArea").innerHTML;
  const ventana = window.open("", "PRINT", "width=900,height=650");
  ventana.document.write(`
    <html>
      <head>
        <title>Reporte de Entregas</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <style>
          body { font-family: Arial, sans-serif; margin: 20px; }
          h3 { text-align: center; margin-bottom: 20px; }
          table { width: 100%; border-collapse: collapse; }
          th, td { border: 1px solid #333; padding: 6px; font-size: 13px; text-align: left; }
          th { background: #f8f8f8; }
        </style>
      </head>
      <body>
        <h3>📦 Reporte de Entregas — ${new Date().toLocaleDateString()}</h3>
        ${area}
      </body>
    </html>
  `);
  ventana.document.close();
  ventana.focus();
  ventana.print();
  ventana.close();
}
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
