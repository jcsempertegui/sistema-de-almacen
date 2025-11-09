<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/UsuarioController.php';

if ($_SESSION['rol'] != 'admin') die("Acceso denegado");

$controller = new UsuarioController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre'   => $_POST['nombre'],
        'apellido' => $_POST['apellido'],
        'usuario'  => $_POST['usuario'],
        'contraseña'    => $_POST['contraseña'],
        'rol'      => $_POST['rol']
    ];
    $controller->crear($data);
    header("Location: listar.php?msg=Usuario creado correctamente");
    exit;
}
include_once __DIR__ . '/../../includes/header.php';

?>

<div class="container mt-4">
  <h2>➕ Nuevo Usuario</h2>
  <form method="POST" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre</label>
      <input type="text" class="form-control" id="nombre" name="nombre" required>
    </div>
    <div class="mb-3">
      <label for="apellido" class="form-label">Apellido</label>
      <input type="text" class="form-control" id="apellido" name="apellido" required>
    </div>
    <div class="mb-3">
      <label for="usuario" class="form-label">Usuario</label>
      <input type="text" class="form-control" id="usuario" name="usuario" required>
    </div>
    <div class="mb-3">
      <label for="contraseña" class="form-label">Contraseña</label>
      <input type="password" class="form-control" id="contraseña" name="contraseña" required>
    </div>
    <div class="mb-3">
      <label for="rol" class="form-label">Rol</label>
      <select class="form-select" id="rol" name="rol" required>
        <option value="admin">Admin</option>
        <option value="usuario">Usuario</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">💾 Guardar</button>
    <a href="listar.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
