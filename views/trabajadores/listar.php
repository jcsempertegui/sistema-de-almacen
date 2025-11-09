<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/TrabajadorController.php';
include_once __DIR__ . '/../../includes/header.php';

$controller = new TrabajadorController($conn);
$trabajadores = $controller->listar();
?>

<div class="d-flex justify-content-between mb-3">
<h2>👷 Trabajadores</h2>
<?php if (isset($_GET['msg'])): ?>
  <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>
<a href="crear.php" class="btn btn-success">➕ Nuevo Trabajador</a>
</div>

<div class="container mt-4">
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Apellidos</th>
        <th>Cargo</th>
        <th>Nacimiento</th>
        <th>Teléfono</th>
        <?php if ($_SESSION['rol'] == 'admin'): ?><th>Acciones</th><?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($trabajadores as $t): ?>
        <tr>
          <td><?= $t['id'] ?></td>
          <td><?= htmlspecialchars($t['nombre']) ?></td>
          <td><?= htmlspecialchars($t['apellido_paterno'] . " " . $t['apellido_materno']) ?></td>
          <td><?= htmlspecialchars($t['cargo']) ?></td>
          <td><?= htmlspecialchars($t['nacimiento']) ?></td>
          <td><?= htmlspecialchars($t['telefono']) ?></td>
          <?php if ($_SESSION['rol'] == 'admin'): ?>
          <td>
            <a href="editar.php?id=<?= $t['id'] ?>" class="btn btn-warning btn-sm">✏ Editar</a>
            <a href="eliminar.php?id=<?= $t['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar trabajador?')">🗑 Eliminar</a>
          </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
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
