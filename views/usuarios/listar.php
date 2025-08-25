<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/UsuarioController.php';
include_once __DIR__ . '/../../includes/header.php';

if ($_SESSION['rol'] != 'admin') die("Acceso denegado");

$controller = new UsuarioController($conn);
$usuarios = $controller->listar();
?>
<h2>Gestión de Usuarios</h2>
<a href="crear.php" class="btn btn-primary mb-3">➕ Nuevo Usuario</a>
<table class="table table-striped">
  <thead><tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Usuario</th><th>Rol</th><th>Acciones</th></tr></thead>
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
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
