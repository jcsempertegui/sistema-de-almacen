<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/RemitoController.php';
include_once __DIR__ . '/../../includes/header.php';

$controller = new RemitoController($conn);

$filtroFecha = $_GET['fecha'] ?? '';
$filtroUsuario = $_GET['usuario_id'] ?? '';
$remitos = $controller->listar($filtroFecha, $filtroUsuario);
$usuarios = $controller->listarUsuarios();
?>

<div class="container mt-4">
  <h2>📑 Remitos</h2>

  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-3">
      <label class="form-label">Fecha</label>
      <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($filtroFecha) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Usuario</label>
      <select name="usuario_id" class="form-select">
        <option value="">Todos</option>
        <?php foreach ($usuarios as $u): ?>
          <option value="<?= $u['id'] ?>" <?= $filtroUsuario == $u['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($u['usuario']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3 align-self-end">
      <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
      <a href="listar.php" class="btn btn-secondary">❌ Limpiar</a>
    </div>
  </form>

  <a href="crear.php" class="btn btn-success mb-3">➕ Nuevo Remito</a>

  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Número</th>
          <th>Tipo</th>
          <th>Usuario</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($remitos as $r): ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['numero']) ?></td>
            <td><?= htmlspecialchars($r['tipo']) ?></td>
            <td><?= htmlspecialchars($r['registrado_por']) ?></td>
            <td><?= htmlspecialchars($r['fecha']) ?></td>
            <td>
              <a href="ver.php?id=<?= $r['id'] ?>" class="btn btn-info btn-sm">👁 Ver</a>
              <a href="editar.php?id=<?= $r['id'] ?>" class="btn btn-warning btn-sm">✏ Editar</a>
              <a href="eliminar.php?id=<?= $r['id'] ?>" class="btn btn-danger btn-sm"
                 onclick="return confirm('¿Eliminar este remito?')">🗑 Eliminar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
