<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/RemitoController.php';
include_once __DIR__ . '/../../includes/header.php';

$controller = new RemitoController($conn);

// Filtros
$filtros = [
  'desde'          => $_GET['desde']          ?? '',
  'hasta'          => $_GET['hasta']          ?? '',
  'usuario_id'     => $_GET['usuario_id']     ?? '',
  'tipo_remito_id' => $_GET['tipo_remito_id'] ?? '',
  'numero_remito'  => $_GET['numero_remito']  ?? '',
  'campo'          => $_GET['campo']          ?? '',
];
$remitos   = $controller->listar($filtros);
$usuarios  = $controller->listarUsuarios();
$tipos     = $controller->listarTipos();
?>

<div class="container mt-3">
  <h2>📄 Remitos</h2>

  <form class="row g-2 mb-3" method="get">
    <div class="col-md-2">
      <label class="form-label">Desde</label>
      <input type="date" name="desde" class="form-control" value="<?= htmlspecialchars($filtros['desde']) ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label">Hasta</label>
      <input type="date" name="hasta" class="form-control" value="<?= htmlspecialchars($filtros['hasta']) ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label">Usuario</label>
      <select name="usuario_id" class="form-select">
        <option value="">Todos</option>
        <?php foreach ($usuarios as $u): ?>
          <option value="<?= $u['id'] ?>" <?= ($filtros['usuario_id']==$u['id']?'selected':'') ?>><?= htmlspecialchars($u['usuario']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Tipo</label>
      <select name="tipo_remito_id" class="form-select">
        <option value="">Todos</option>
        <?php foreach ($tipos as $t): ?>
          <option value="<?= $t['id'] ?>" <?= ($filtros['tipo_remito_id']==$t['id']?'selected':'') ?>><?= htmlspecialchars($t['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">N° Remito</label>
      <input type="text" name="numero_remito" class="form-control" value="<?= htmlspecialchars($filtros['numero_remito']) ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label">Campo</label>
      <input type="text" name="campo" class="form-control" value="<?= htmlspecialchars($filtros['campo']) ?>">
    </div>
    <div class="col-12 d-flex gap-2 mt-2">
      <button class="btn btn-secondary">Filtrar</button>
      <a class="btn btn-outline-secondary" href="listar.php">Limpiar</a>
      <?php if ($_SESSION['rol'] === 'admin'): ?>
        <a href="crear.php" class="btn btn-primary ms-auto">➕ Nuevo Remito</a>
      <?php endif; ?>
    </div>
  </form>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          <th>Fecha</th>
          <th>N° Remito</th>
          <th>Tipo</th>
          <th>Campo</th>
          <th>Usuario</th>
          <th>Ítems</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($remitos as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['fecha']) ?></td>
          <td><?= htmlspecialchars($r['numero_remito']) ?></td>
          <td><?= htmlspecialchars($r['tipo']) ?></td>
          <td><?= htmlspecialchars($r['campo']) ?></td>
          <td><?= htmlspecialchars($r['usuario']) ?></td>
          <td><?= (int)$r['items'] ?></td>
          <td class="d-flex gap-2">
            <a href="ver.php?id=<?= $r['id'] ?>" class="btn btn-info btn-sm">👁 Ver</a>
            <?php if ($_SESSION['rol'] === 'admin'): ?>
              <a href="editar.php?id=<?= $r['id'] ?>" class="btn btn-warning btn-sm">✏️ Editar</a>
              <a href="eliminar.php?id=<?= $r['id'] ?>" class="btn btn-danger btn-sm"
                 onclick="return confirm('¿Eliminar remito y revertir stock?')">🗑 Eliminar</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($remitos)): ?>
        <tr><td colspan="7" class="text-center">Sin resultados</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
