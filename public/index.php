<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../includes/header.php';

// ===============================
// 📊 CONSULTAS DE ESTADÍSTICAS
// ===============================

// Total de productos
$totalProductos = $conn->query("SELECT COUNT(*) AS total FROM producto")->fetch_assoc()['total'] ?? 0;

// Total de entregas
$totalEntregas = $conn->query("SELECT COUNT(*) AS total FROM entrega")->fetch_assoc()['total'] ?? 0;

// Total de remitos (entradas)
$totalRemitos = $conn->query("SELECT COUNT(*) AS total FROM remito")->fetch_assoc()['total'] ?? 0;

// Productos con stock bajo (<=3)
$productosBajos = $conn->query("
    SELECT p.nombre,
           IFNULL(GROUP_CONCAT(CONCAT(a.nombre, ': ', ap.valor) SEPARATOR ', '), '') AS atributos,
           p.stock
    FROM producto p
    LEFT JOIN atributo_producto ap ON ap.producto_id = p.id
    LEFT JOIN atributo a ON a.id = ap.atributo_id
    WHERE p.stock <= 5
    GROUP BY p.id
    ORDER BY p.stock ASC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Últimas entregas
$ultimasEntregas = $conn->query("
    SELECT e.fecha, CONCAT(t.nombre, ' ', t.apellido_paterno) AS trabajador, e.inspector
    FROM entrega e
    INNER JOIN trabajador t ON e.trabajador_id = t.id
    ORDER BY e.fecha DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Entregas por mes (últimos 6 meses)
$entregasPorMes = $conn->query("
    SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, COUNT(*) AS total
    FROM entrega
    WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY mes
    ORDER BY mes ASC
")->fetch_all(MYSQLI_ASSOC);

// Productos más entregados (con atributos únicos)
$productosMasEntregados = $conn->query("
    SELECT 
        CONCAT(
            p.nombre,
            IFNULL(
                CONCAT(' — ', GROUP_CONCAT(DISTINCT CONCAT(a.nombre, ': ', ap.valor) SEPARATOR ', ')),
                ''
            )
        ) AS nombre,
        SUM(de.cantidad) AS total
    FROM detalle_entrega de
    INNER JOIN producto p ON de.producto_id = p.id
    LEFT JOIN atributo_producto ap ON ap.producto_id = p.id
    LEFT JOIN atributo a ON a.id = ap.atributo_id
    GROUP BY p.id
    ORDER BY total DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container py-5">

  <!-- Encabezado con logo -->
  <div class="text-center mb-5">
    <img src="img/cp.png" alt="Logo Almacén" class="mb-3" style="max-width:250px; height:auto;">
    <h2 class="fw-bold text-primary mt-2">Sistema de Gestión de Almacén</h2>
    <p class="text-muted fs-5">Monitoreo general de stock, entregas y remitos</p>
    <hr class="w-50 mx-auto">
  </div>

  <!-- Tarjetas de estadísticas -->
  <div class="row g-4 mb-5">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm text-center p-4">
        <i class="fa fa-box fa-3x text-primary mb-2"></i>
        <h5 class="fw-bold">Productos</h5>
        <h3 class="text-primary"><?= $totalProductos ?></h3>
        <a href="views/productos/listar.php" class="btn btn-outline-primary btn-sm mt-2">Ver detalles</a>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm text-center p-4">
        <i class="fa fa-hand-holding fa-3x text-success mb-2"></i>
        <h5 class="fw-bold">Entregas</h5>
        <h3 class="text-success"><?= $totalEntregas ?></h3>
        <a href="views/entregas/listar.php" class="btn btn-outline-success btn-sm mt-2">Ver entregas</a>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm text-center p-4">
        <i class="fa fa-truck fa-3x text-warning mb-2"></i>
        <h5 class="fw-bold">Entradas (Remitos)</h5>
        <h3 class="text-warning"><?= $totalRemitos ?></h3>
        <a href="views/remitos/listar.php" class="btn btn-outline-warning btn-sm mt-2">Ver remitos</a>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm text-center p-4">
        <i class="fa fa-user-circle fa-3x text-info mb-2"></i>
        <h5 class="fw-bold">Usuario activo</h5>
        <h3 class="text-info"><?= htmlspecialchars($_SESSION['usuario']) ?></h3>
        <a href="logout.php" class="btn btn-outline-danger btn-sm mt-2">Cerrar sesión</a>
      </div>
    </div>
  </div>

  <!-- Gráficos -->
  <div class="row g-4 mb-5">
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold">
          <i class="fa fa-chart-line"></i> Entregas en los últimos 6 meses
        </div>
        <div class="card-body">
          <canvas id="graficoEntregasMes"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white fw-bold">
          <i class="fa fa-boxes"></i> Productos más entregados
        </div>
        <div class="card-body">
          <canvas id="graficoProductosMasEntregados"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Tablas -->
  <div class="row g-4">
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-danger text-white fw-bold">
          <i class="fa fa-exclamation-triangle"></i> Productos con bajo stock
        </div>
        <div class="card-body p-0">
          <table class="table table-striped mb-0">
            <thead class="table-light">
              <tr><th>Producto</th><th>Stock</th></tr>
            </thead>
            <tbody>
              <?php if (count($productosBajos)): ?>
                <?php foreach ($productosBajos as $p): ?>
                  <tr>
                    <td><?= htmlspecialchars($p['nombre'] . ($p['atributos'] ? ' — ' . $p['atributos'] : '')) ?></td>
                    <td><span class="badge bg-danger"><?= $p['stock'] ?></span></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="2" class="text-center text-muted">Sin alertas de stock</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-secondary text-white fw-bold">
          <i class="fa fa-clock"></i> Últimas entregas
        </div>
        <div class="card-body p-0">
          <table class="table table-striped mb-0">
            <thead class="table-light">
              <tr><th>Fecha</th><th>Trabajador</th><th>Inspector</th></tr>
            </thead>
            <tbody>
              <?php if (count($ultimasEntregas)): ?>
                <?php foreach ($ultimasEntregas as $e): ?>
                  <tr>
                    <td><?= htmlspecialchars($e['fecha']) ?></td>
                    <td><?= htmlspecialchars($e['trabajador']) ?></td>
                    <td><?= htmlspecialchars($e['inspector']) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="3" class="text-center text-muted">Sin entregas registradas</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<footer class="mt-5">
  <p class="text-center text-muted mb-0">
    © <?= date('Y') ?> Sistema de Gestión de Almacén — Desarrollado por <strong>Julio César Sempertegui</strong>
  </p>
</footer>

<!-- Librerías -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Gráficos -->
<script>
const ctxMes = document.getElementById('graficoEntregasMes');
const ctxProductos = document.getElementById('graficoProductosMasEntregados');

const entregasData = <?= json_encode($entregasPorMes) ?>;
const productosData = <?= json_encode($productosMasEntregados) ?>;

// 📈 Gráfico de entregas por mes
new Chart(ctxMes, {
  type: 'line',
  data: {
    labels: entregasData.map(e => e.mes),
    datasets: [{
      label: 'Entregas por mes',
      data: entregasData.map(e => e.total),
      borderColor: '#007bff',
      backgroundColor: 'rgba(0, 123, 255, 0.2)',
      tension: 0.3,
      fill: true
    }]
  },
  options: {
    responsive: true,
    scales: { y: { beginAtZero: true } },
    plugins: { legend: { display: false } }
  }
});

// 📊 Gráfico de productos más entregados
new Chart(ctxProductos, {
  type: 'bar',
  data: {
    labels: productosData.map(p => p.nombre.length > 25 ? p.nombre.substring(0, 25) + '…' : p.nombre),
    datasets: [{
      label: 'Cantidad entregada',
      data: productosData.map(p => p.total),
      backgroundColor: ['#28a745','#17a2b8','#ffc107','#007bff','#dc3545','#6610f2','#6c757d','#20c997','#fd7e14','#0dcaf0']
    }]
  },
  options: {
    responsive: true,
    scales: {
      x: {
        ticks: {
          autoSkip: false,
          maxRotation: 40,
          minRotation: 40,
          font: { size: 11 }
        }
      },
      y: { beginAtZero: true }
    },
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          title: (context) => productosData[context[0].dataIndex].nombre
        }
      }
    }
  }
});
</script>

</body>
</html>
