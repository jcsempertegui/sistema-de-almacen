<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/UsuarioController.php';
include_once __DIR__ . '/../../includes/header.php';

if ($_SESSION['rol'] != 'admin') die("Acceso denegado");

$controller = new UsuarioController($conn);
$usuarios = $controller->listar();
?>
<div class="d-flex justify-content-between mb-3">
<h2>Gestión de Usuarios</h2>
<?php if (isset($_GET['msg'])): ?>
  <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>
<a href="crear.php" class="btn btn-success">➕ Nuevo Usuario</a>
</div>

<div class="container mt-4">
<table class="table table-bordered table-striped">
  <thead class="table-dark"><tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Usuario</th><th>Rol</th><th>Acciones</th></tr></thead>
  <tbody>
    <?php foreach($usuarios as $u): ?>
    <tr>
      <td><?= $u['id'] ?></td>
      <td><?= $u['nombre'] ?></td>
      <td><?= $u['apellido'] ?></td>
      <td><?= $u['usuario'] ?></td>
      <td><?= $u['rol'] ?></td>
      <td>
        <a href="editar.php?id=<?= $u['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
        <a href="eliminar.php?id=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')">Eliminar</a>
      </td>
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
