<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/RemitoController.php';
include_once __DIR__ . '/../../includes/header.php';

$controller = new RemitoController($conn);

// Filtros
$inicio   = $_GET['inicio']   ?? '';
$fin      = $_GET['fin']      ?? '';
$numero   = $_GET['numero']   ?? '';
$tipo     = $_GET['tipo']     ?? '';
$usuario  = $_GET['usuario']  ?? '';

$remitos = $controller->listarAvanzado($inicio, $fin, $numero, $tipo, $usuario);
$tipos   = $controller->listarTipos();
$usuarios = $controller->listarUsuarios();
?>

<div class="d-flex justify-content-between mb-3">
  <h2>📑 Remitos</h2>
  <?php if (!empty($_GET['msg'])): ?>
  <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    <?= htmlspecialchars($_GET['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php elseif (!empty($_GET['error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
    <?= htmlspecialchars($_GET['error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

  <a href="crear.php" class="btn btn-success">➕ Nuevo Remito</a>
</div>

<form method="GET" class="card card-body mb-3">
  <div class="row g-2">
    <div class="col-md-2">
      <label class="form-label">Desde</label>
      <input type="date" name="inicio" class="form-control" value="<?= htmlspecialchars($inicio) ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label">Hasta</label>
      <input type="date" name="fin" class="form-control" value="<?= htmlspecialchars($fin) ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label">Número</label>
      <input type="text" name="numero" class="form-control" value="<?= htmlspecialchars($numero) ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label">Tipo</label>
      <select name="tipo" class="form-select">
        <option value="">Todos</option>
        <?php foreach ($tipos as $t): ?>
          <option value="<?= $t['id'] ?>" <?= $tipo==$t['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($t['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Usuario</label>
      <select name="usuario" class="form-select">
        <option value="">Todos</option>
        <?php foreach ($usuarios as $u): ?>
          <option value="<?= $u['id'] ?>" <?= $usuario==$u['id'] ? 'selected' : '' ?>>
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

<table class="table table-bordered table-hover">
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
          <?php if ($_SESSION['rol'] == 'admin'): ?>
          <a href="editar.php?id=<?= $r['id'] ?>" class="btn btn-warning btn-sm">✏ Editar</a>
          <a href="eliminar.php?id=<?= $r['id'] ?>" class="btn btn-danger btn-sm"
             onclick="return confirm('¿Seguro de eliminar este remito?')">🗑 Eliminar</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

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
