<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/BackupController.php';

$controller = new BackupController($conn);

if (isset($_GET['accion'])) {
    switch ($_GET['accion']) {
        case 'generar': $controller->generar(); break;
        case 'restaurar': $controller->restaurar($_GET['archivo']); break;
        case 'eliminar': $controller->eliminar($_GET['archivo']); break;
        case 'guardar': $controller->guardarConfiguracion($_POST['frecuencia']); break;
    }
}

$backups = $controller->model->listarBackups();
$frecuencia = $controller->model->obtenerConfiguracion();

include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
  <h2>💾 Gestión de Backups</h2>

  <?php if (isset($_GET['mensaje'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['mensaje']) ?></div>
  <?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
  <?php endif; ?>

  <div class="card p-3 mb-4">
    <h5>⚙️ Configuración automática</h5>
    <form method="POST" action="?accion=guardar" class="row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label">Frecuencia de respaldo</label>
        <select name="frecuencia" class="form-select">
          <option value="manual" <?= $frecuencia == 'manual' ? 'selected' : '' ?>>Manual</option>
          <option value="diario" <?= $frecuencia == 'diario' ? 'selected' : '' ?>>Diario</option>
          <option value="semanal" <?= $frecuencia == 'semanal' ? 'selected' : '' ?>>Semanal</option>
          <option value="mensual" <?= $frecuencia == 'mensual' ? 'selected' : '' ?>>Mensual</option>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary">💾 Guardar</button>
      </div>
      <div class="col-md-6 text-end">
        <a href="?accion=generar" class="btn btn-success">🧩 Generar Backup</a>
      </div>
    </form>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <h5>📁 Backups disponibles</h5>
      <div class="table-responsive">
        <table class="table table-striped table-bordered mt-3">
          <thead class="table-dark">
            <tr>
              <th>Archivo</th>
              <th>Fecha</th>
              <th>Tamaño</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($backups)): ?>
              <tr><td colspan="4" class="text-center">No hay backups disponibles</td></tr>
            <?php else: ?>
              <?php foreach ($backups as $b): ?>
                <?php $path = __DIR__ . '/../../backups/' . $b; ?>
                <tr>
                  <td><?= htmlspecialchars($b) ?></td>
                  <td><?= date('Y-m-d H:i:s', filemtime($path)) ?></td>
                  <td><?= round(filesize($path) / 1024, 2) ?> KB</td>
                  <td>
                    <a href="?accion=restaurar&archivo=<?= urlencode($b) ?>" class="btn btn-sm btn-warning">♻ Restaurar</a>
                    <a href="?accion=eliminar&archivo=<?= urlencode($b) ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar backup?')">🗑 Eliminar</a>
                    <a href="../../backups/<?= urlencode($b) ?>" class="btn btn-sm btn-info" download>⬇ Descargar</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
