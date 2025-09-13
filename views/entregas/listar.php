<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/EntregaController.php';
include_once __DIR__ . '/../../includes/header.php';

$controller = new EntregaController($conn);

$filtroFecha = $_GET['fecha'] ?? '';
$filtroTrabajador = $_GET['trabajador_id'] ?? '';
$entregas = $controller->listar($filtroFecha, $filtroTrabajador);
$trabajadores = $controller->listarTrabajadores();
?>

<div class="container mt-4">
  <h2>📦 Entregas a Trabajadores</h2>

  <form method="GET" class="row g-3 mb-3">
    <div class="col-md-3">
      <label class="form-label">Fecha</label>
      <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($filtroFecha) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Trabajador</label>
      <select name="trabajador_id" class="form-select">
        <option value="">Todos</option>
        <?php foreach ($trabajadores as $t): ?>
          <option value="<?= $t['id'] ?>" <?= $filtroTrabajador == $t['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($t['nombre'] . ' ' . $t['apellido_paterno'] . ' ' . $t['apellido_materno']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-5 align-self-end">
      <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
      <a href="listar.php" class="btn btn-secondary">❌ Limpiar</a>
      <a href="crear.php" class="btn btn-primary">➕ Nueva Entrega</a>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-striped table-bordered">
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