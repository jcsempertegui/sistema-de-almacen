<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/UsuarioController.php';
include_once __DIR__ . '/../../includes/header.php';

if ($_SESSION['rol'] != 'admin') die("Acceso denegado");

$controller = new UsuarioController($conn);
$usuario = $controller->obtener($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre'   => $_POST['nombre'],
        'apellido' => $_POST['apellido'],
        'usuario'  => $_POST['usuario'],
        'rol'      => $_POST['rol']
    ];
    $controller->editar($_GET['id'], $data);
    header("Location: listar.php?msg=Usuario actualizado");
    exit;
}
?>

<div class="container mt-4">
  <h2>✏️ Editar Usuario</h2>
  <form method="POST" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre</label>
      <input type="text" class="form-control" id="nombre" name="nombre" value="<?= $usuario['nombre'] ?>" required>
    </div>
    <div class="mb-3">
      <label for="apellido" class="form-label">Apellido</label>
      <input type="text" class="form-control" id="apellido" name="apellido" value="<?= $usuario['apellido'] ?>" required>
    </div>
    <div class="mb-3">
      <label for="usuario" class="form-label">Usuario</label>
      <input type="text" class="form-control" id="usuario" name="usuario" value="<?= $usuario['usuario'] ?>" required>
    </div>
    <div class="mb-3">
      <label for="rol" class="form-label">Rol</label>
      <select class="form-select" id="rol" name="rol" required>
        <option value="admin" <?= $usuario['rol']=='admin'?'selected':'' ?>>Admin</option>
        <option value="usuario" <?= $usuario['rol']=='usuario'?'selected':'' ?>>Usuario</option>
      </select>
    </div>
    <button type="submit" class="btn btn-success">💾 Actualizar</button>
    <a href="listar.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
