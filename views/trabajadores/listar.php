<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/TrabajadorController.php';
include_once __DIR__ . '/../../includes/header.php';

$controller = new TrabajadorController($conn);
$trabajadores = $controller->listar();
?>

<div class="container mt-4">
  <h2>👷 Trabajadores</h2>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>

  <a href="crear.php" class="btn btn-primary mb-3">➕ Nuevo Trabajador</a>

  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Apellidos</th>
        <th>Cargo</th>
        <th>Nacimiento</th>
        <th>Teléfono</th>
        <th>Acciones</th>
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
          <td>
            <a href="editar.php?id=<?= $t['id'] ?>" class="btn btn-warning btn-sm">✏ Editar</a>
            <a href="eliminar.php?id=<?= $t['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar trabajador?')">🗑 Eliminar</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
