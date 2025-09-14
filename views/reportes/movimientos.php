<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
include_once __DIR__ . '/../../includes/header.php';

// Filtros
$filtroProducto = $_GET['producto_id'] ?? '';
$filtroCategoria = $_GET['categoria_id'] ?? '';
$filtroFechaIni = $_GET['fecha_ini'] ?? '';
$filtroFechaFin = $_GET['fecha_fin'] ?? '';
$export = $_GET['export'] ?? ''; // excel | pdf

// Query base
$sql = "
SELECT 
    p.id,
    p.nombre,
    c.nombre AS categoria,
    COALESCE(SUM(dr.cantidad), 0) AS total_entradas,
    COALESCE(SUM(de.cantidad), 0) AS total_salidas,
    p.stock AS stock_actual
FROM producto p
LEFT JOIN categoria c ON p.categoria_id = c.id
LEFT JOIN detalle_remito dr ON p.id = dr.producto_id
LEFT JOIN remito r ON dr.remito_id = r.id
LEFT JOIN detalle_entrega de ON p.id = de.producto_id
LEFT JOIN entrega e ON de.entrega_id = e.id
WHERE 1=1
";

$params = [];
$types  = "";

if ($filtroProducto !== "") {
    $sql .= " AND p.id = ? ";
    $params[] = $filtroProducto;
    $types   .= "i";
}

if ($filtroCategoria !== "") {
    $sql .= " AND c.id = ? ";
    $params[] = $filtroCategoria;
    $types   .= "i";
}

if ($filtroFechaIni !== "" && $filtroFechaFin !== "") {
    $sql .= " AND (
                 (r.fecha BETWEEN ? AND ?)
              OR (e.fecha BETWEEN ? AND ?)
             ) ";
    $params[] = $filtroFechaIni;
    $params[] = $filtroFechaFin;
    $params[] = $filtroFechaIni;
    $params[] = $filtroFechaFin;
    $types   .= "ssss";
}

$sql .= " GROUP BY p.id, p.nombre, c.nombre, p.stock
          ORDER BY p.nombre ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);

// Exportar Excel
if ($export === 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=reporte_movimientos.xls");
    echo "Producto\tCategoría\tEntradas\tSalidas\tStock Actual\n";
    foreach ($rows as $r) {
        echo "{$r['nombre']}\t{$r['categoria']}\t{$r['total_entradas']}\t{$r['total_salidas']}\t{$r['stock_actual']}\n";
    }
    exit;
}

// Exportar PDF (usando FPDF)
if ($export === 'pdf') {
    require_once __DIR__ . '/../../vendor/autoload.php'; // asegúrate de tener FPDF o Dompdf instalado
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,10,'Reporte de Movimientos de Inventario',0,1,'C');
    $pdf->Ln(5);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(50,8,'Producto',1);
    $pdf->Cell(40,8,'Categoria',1);
    $pdf->Cell(30,8,'Entradas',1);
    $pdf->Cell(30,8,'Salidas',1);
    $pdf->Cell(30,8,'Stock',1);
    $pdf->Ln();
    $pdf->SetFont('Arial','',10);
    foreach ($rows as $r) {
        $pdf->Cell(50,8,$r['nombre'],1);
        $pdf->Cell(40,8,$r['categoria'],1);
        $pdf->Cell(30,8,$r['total_entradas'],1);
        $pdf->Cell(30,8,$r['total_salidas'],1);
        $pdf->Cell(30,8,$r['stock_actual'],1);
        $pdf->Ln();
    }
    $pdf->Output();
    exit;
}

// Combos para filtros
$productos = $conn->query("SELECT id, nombre FROM producto ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);
$categorias = $conn->query("SELECT id, nombre FROM categoria ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mt-4">
  <h2>📊 Reporte de Movimientos de Inventario</h2>

  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-3">
      <label class="form-label">Producto</label>
      <select name="producto_id" class="form-select">
        <option value="">Todos</option>
        <?php foreach ($productos as $p): ?>
          <option value="<?= $p['id'] ?>" <?= ($filtroProducto == $p['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($p['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label">Categoría</label>
      <select name="categoria_id" class="form-select">
        <option value="">Todas</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= $c['id'] ?>" <?= ($filtroCategoria == $c['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label">Fecha inicio</label>
      <input type="date" name="fecha_ini" class="form-control" value="<?= htmlspecialchars($filtroFechaIni) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Fecha fin</label>
      <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($filtroFechaFin) ?>">
    </div>

    <div class="col-12">
      <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
      <a href="movimientos.php" class="btn btn-secondary">🔄 Reset</a>
      <a href="movimientos.php?<?= http_build_query(array_merge($_GET, ['export'=>'excel'])) ?>" class="btn btn-success">⬇ Excel</a>
      <a href="movimientos.php?<?= http_build_query(array_merge($_GET, ['export'=>'pdf'])) ?>" class="btn btn-danger">⬇ PDF</a>
    </div>
  </form>

  <table class="table table-striped table-bordered">
    <thead>
      <tr>
        <th>Producto</th>
        <th>Categoría</th>
        <th>Total Entradas</th>
        <th>Total Salidas</th>
        <th>Stock Actual</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="5" class="text-center">No hay datos con los filtros aplicados</td></tr>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['nombre']) ?></td>
            <td><?= htmlspecialchars($r['categoria']) ?></td>
            <td><?= (int)$r['total_entradas'] ?></td>
            <td><?= (int)$r['total_salidas'] ?></td>
            <td><?= (int)$r['stock_actual'] ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
