<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ReporteController.php';
include_once __DIR__ . '/../../includes/header.php';

$controller = new ReporteController($conn);

// Capturar filtros
$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin    = $_GET['fecha_fin'] ?? '';
$usuarioId   = $_GET['usuario_id'] ?? '';
$productoId  = $_GET['producto_id'] ?? '';
$numero      = trim($_GET['numero'] ?? '');

// Obtener datos
try {
    $salidas  = $controller->salidas($fechaInicio, $fechaFin, $usuarioId, $productoId, $numero);
} catch (Exception $ex) {
    $salidas = [];
    $error = $ex->getMessage();
}

$usuarios  = $controller->listarUsuarios();
$productos = $controller->listarProductos();
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

<div class="container mt-4 ">
  <h2>📤 Reporte de Salidas (Remitos de Egreso)</h2>

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
      <div class="col-md-2">
        <label class="form-label">N° Remito</label>
        <input type="text" name="numero" class="form-control" value="<?= htmlspecialchars($numero) ?>">
      </div>
      <div class="col-md-1 d-flex align-items-end">
        <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
        <a href="salidas.php" class="btn btn-secondary">❌ Limpiar</a>
        <button type="button" onclick="imprimirReporte()" class="btn btn-success">🖨 Imprimir</button>
      </div>
    </div>
  </form>

  <!-- 📊 TABLA DE RESULTADOS -->
  <div class="card shadow-sm" id="reporteArea">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-bordered mb-0">
          <thead class="table-dark">
            <tr>
              <th>Fecha</th>
              <th>N° Remito</th>
              <th>Producto</th>
              <th>Cantidad</th>
              <th>Usuario</th>
              <th>Campo</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($salidas)): ?>
            <tr><td colspan="6" class="text-center">No se encontraron resultados</td></tr>
          <?php else: ?>
            <?php foreach ($salidas as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['fecha']) ?></td>
                <td><?= htmlspecialchars($row['numero']) ?></td>
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

<!-- 🖨 SCRIPT DE IMPRESIÓN -->
<script>
function imprimirReporte() {
  const area = document.getElementById("reporteArea").innerHTML;
  const ventana = window.open("", "PRINT", "width=900,height=650");
  ventana.document.write(`
    <html>
      <head>
        <title>Reporte de Salidas</title>
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
        <h3>📤 Reporte de Salidas — ${new Date().toLocaleDateString()}</h3>
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
