<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/RemitoController.php';
include_once __DIR__ . '/../../includes/header.php';

$controller = new RemitoController($conn);

// Filtros
$filtroFecha = $_GET['fecha'] ?? '';
$filtroUsuario = $_GET['usuario'] ?? '';
$remitos = $controller->listar($filtroFecha, $filtroUsuario);
$usuarios = $controller->listarUsuarios();
?>

<div class="container mt-4">
  <h2>📄 Remitos</h2>

  <form class="row g-3 mb-3" method="GET">
    <div class="col-md-3">
      <label for="fecha" class="form-label">Fecha</label>
      <input type="date" name="fecha" id="fecha" class="form-control" value="<?= htmlspecialchars($filtroFecha) ?>">
    </div>
    <div class="col-md-3">
      <label for="usuario" class="form-label">Usuario</label>
      <select name="usuario" id="usuario" class="form-select">
        <option value="">Todos</option>
        <?php foreach ($usuarios as $u): ?>
          <option value="<?= $u['id'] ?>" <?= ($filtroUsuario == $u['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($u['usuario']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
    </div>
  </form>

  <?php if ($_SESSION['rol'] == 'admin'): ?>
    <a href="crear.php" class="btn btn-success mb-3">➕ Nuevo Remito</a>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Número</th>
          <th>Fecha</th>
          <th>Tipo</th>
          <th>Usuario</th>
          <th>Observaciones</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($remitos as $r): ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['numero']) ?></td>
            <td><?= htmlspecialchars($r['fecha']) ?></td>
            <td><?= htmlspecialchars($r['tipo']) ?></td>
            <td><?= htmlspecialchars($r['usuario']) ?></td>
            <td><?= htmlspecialchars($r['observaciones']) ?></td>
            <td>
              <a href="ver.php?id=<?= $r['id'] ?>" class="btn btn-info btn-sm">👁 Ver</a>
              <?php if ($_SESSION['rol'] == 'admin'): ?>
                <a href="editar.php?id=<?= $r['id'] ?>" class="btn btn-warning btn-sm">✏️ Editar</a>
                <a href="eliminar.php?id=<?= $r['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('¿Seguro que deseas eliminar este remito?')">🗑 Eliminar</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($remitos)): ?>
          <tr><td colspan="7" class="text-center">No hay remitos registrados</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
