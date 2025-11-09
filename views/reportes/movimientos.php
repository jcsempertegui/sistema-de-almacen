<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ReporteController.php';


$controller = new ReporteController($conn);

// 📅 Filtros
$fechaInicio  = $_GET['fecha_inicio'] ?? '';
$fechaFin     = $_GET['fecha_fin'] ?? '';
$categoriaId  = $_GET['categoria_id'] ?? '';
$productoId   = $_GET['producto_id'] ?? '';

try {
    $movimientos = $controller->movimientos($fechaInicio, $fechaFin, $categoriaId, $productoId);
} catch (Exception $ex) {
    $movimientos = [];
    $error = $ex->getMessage();
}

$categorias = $controller->listarCategorias();
$productos  = $controller->listarProductos();
include_once __DIR__ . '/../../includes/header.php';
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
  <h2>🔄 Reporte de Movimientos de Inventario</h2>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- 🔍 FILTROS -->
<form method="GET" class="card card-body mb-3 filtros-card">
  <div class="row g-3 align-items-end">
    <div class="col-12 col-sm-6 col-md-2">
      <label class="form-label">Fecha inicio</label>
      <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fechaInicio) ?>">
    </div>
    <div class="col-12 col-sm-6 col-md-2">
      <label class="form-label">Fecha fin</label>
      <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fechaFin) ?>">
    </div>
    <div class="col-12 col-sm-6 col-md-2">
      <label class="form-label">Categoría</label>
      <select name="categoria_id" class="form-select">
        <option value="">Todas</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= $c['id'] ?>" <?= ($categoriaId == $c['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
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
    <div class="col-12 col-md-3 d-flex justify-content-end align-items-end gap-2 ms-auto">
      <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
      <a href="movimientos.php" class="btn btn-secondary">❌ Limpiar</a>
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
              <th>Producto</th>
              <th>Atributos</th>
              <th>Categoría</th>
              <th>Total Entradas</th>
              <th>Total Salidas</th>
              <th>Total Entregas</th>
              <th>Stock Actual</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($movimientos)): ?>
            <tr><td colspan="7" class="text-center">No se encontraron resultados</td></tr>
          <?php else: ?>
            <?php foreach ($movimientos as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['producto']) ?></td>
                <td><?= htmlspecialchars($row['atributos'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['categoria']) ?></td>
                <td><?= htmlspecialchars($row['total_entradas']) ?></td>
                <td><?= htmlspecialchars($row['total_salidas']) ?></td>
                <td><?= htmlspecialchars($row['total_entregas']) ?></td>
                <td><?= htmlspecialchars($row['stock_actual']) ?></td>
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
  const ventana = window.open("", "PRINT", "width=1000,height=800");
  ventana.document.write(`
    <html>
      <head>
        <title>Reporte de Movimientos</title>
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
        <h3>🔄 Reporte de Movimientos — ${new Date().toLocaleDateString()}</h3>
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
