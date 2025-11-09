<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/EntregaController.php';
include_once __DIR__ . '/../../includes/header.php';

$controller = new EntregaController($conn);

// ✅ Capturar filtros
$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin    = $_GET['fecha_fin'] ?? '';
$trabajador  = $_GET['trabajador'] ?? '';
$usuario     = $_GET['usuario'] ?? '';

// ✅ Obtener datos
$entregas     = $controller->listar($fechaInicio, $fechaFin, $trabajador, $usuario);
$trabajadores = $controller->listarTrabajadores();
$usuarios     = $controller->listarUsuarios();
?>

<div class="d-flex justify-content-between mb-3">
  <h2>📦 Entregas a Trabajadores</h2>
  <?php if (isset($_GET['msg'])): ?>
  <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>
  <a href="crear.php" class="btn btn-success">➕ Nueva Entrega</a>
</div>
  <!-- 🔎 Filtros -->
  <form method="GET" class="card card-body mb-3">
    <div class="row g-2 ">
      <div class="col-md-2">
        <label class="form-label">Fecha inicio</label>
        <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fechaInicio) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Fecha fin</label>
        <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fechaFin) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Trabajador</label>
        <select name="trabajador" class="form-select">
          <option value="">Todos</option>
          <?php foreach ($trabajadores as $t): ?>
            <option value="<?= $t['id'] ?>" <?= $trabajador == $t['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($t['nombre'] . ' ' . $t['apellido_paterno'] . ' ' . $t['apellido_materno']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Usuario</label>
        <select name="usuario" class="form-select">
          <option value="">Todos</option>
          <?php foreach ($usuarios as $u): ?>
            <option value="<?= $u['id'] ?>" <?= $usuario == $u['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($u['usuario']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-flex align-items-end">
      <button type="submit" class="btn btn-primary me-2">🔍 Filtrar</button>
      <a href="listar.php" class="btn btn-secondary">❌ Limpiar</a>
      </div>
    </div>
  </form>

  <!-- 📋 Tabla -->
  <div class="table-responsive card shadow-sm">
    <table class="table table-striped table-bordered mb-0">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Fecha</th>
          <th>Trabajador</th>
          <th>Inspector</th>
          <th>Usuario</th>
          <th>Campo</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($entregas as $e): ?>
          <tr>
            <td><?= $e['id'] ?></td>
            <td><?= htmlspecialchars($e['fecha']) ?></td>
            <td><?= htmlspecialchars($e['trabajador']) ?></td>
            <td><?= htmlspecialchars($e['inspector']) ?></td>
            <td><?= htmlspecialchars($e['registrado_por']) ?></td>
            <td><?= htmlspecialchars($e['campo']) ?></td>
            <td>
              <a href="ver.php?id=<?= $e['id'] ?>" class="btn btn-info btn-sm">👁 Ver</a>
              <?php if ($_SESSION['rol'] == 'admin'): ?>
                <a href="editar.php?id=<?= $e['id'] ?>" class="btn btn-warning btn-sm">✏ Editar</a>
                <a href="eliminar.php?id=<?= $e['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('¿Eliminar esta entrega?')">🗑 Eliminar</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($entregas)): ?>
          <tr><td colspan="7" class="text-center">No hay entregas registradas</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
  setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) {
      alert.classList.remove('show');
      setTimeout(() => alert.remove(), 500);
    }
  }, 4000); // desaparece después de 4 segundos
</script>
