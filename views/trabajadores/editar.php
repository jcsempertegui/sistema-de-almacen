<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/TrabajadorController.php';
if ($_SESSION['rol'] != 'admin') die("Acceso denegado");

$controller = new TrabajadorController($conn);
$id = $_GET['id'] ?? null;
$trabajador = $controller->obtener($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre'           => $_POST['nombre'],
        'apellido_paterno' => $_POST['apellido_paterno'],
        'apellido_materno' => $_POST['apellido_materno'],
        'cargo'            => $_POST['cargo'],
        'nacimiento'       => $_POST['nacimiento'],
        'telefono'         => $_POST['telefono']
    ];
    $controller->editar($id, $data);
    header("Location: listar.php?msg=Trabajador actualizado correctamente");
    exit;
}
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
  <h2>✏ Editar Trabajador</h2>
  <form method="POST" class="card p-4 shadow-sm">
    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($trabajador['nombre']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Apellido Paterno</label>
        <input type="text" name="apellido_paterno" class="form-control" value="<?= htmlspecialchars($trabajador['apellido_paterno']) ?>" required>
      </div>
    </div>
    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Apellido Materno</label>
        <input type="text" name="apellido_materno" class="form-control" value="<?= htmlspecialchars($trabajador['apellido_materno']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Cargo</label>
        <input type="text" name="cargo" class="form-control" value="<?= htmlspecialchars($trabajador['cargo']) ?>" required>
      </div>
    </div>
    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Nacimiento</label>
        <input type="date" name="nacimiento" class="form-control" value="<?= htmlspecialchars($trabajador['nacimiento']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Teléfono</label>
        <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($trabajador['telefono']) ?>">
      </div>
    </div>
    <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
    <a href="listar.php" class="btn btn-secondary">↩ Cancelar</a>
  </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
