<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/RemitoController.php';
include_once __DIR__ . '/../../includes/header.php';

if ($_SESSION['rol'] != 'admin') {
    die("Acceso denegado");
}

$controller = new RemitoController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'numero' => $_POST['numero'],
        'fecha' => $_POST['fecha'],
        'tipo_remito_id' => $_POST['tipo_remito_id'],
        'usuario_id' => $_SESSION['user_id'],
        'observaciones' => $_POST['observaciones']
    ];

    $detalles = [];
    if (isset($_POST['producto_id'])) {
        foreach ($_POST['producto_id'] as $i => $prodId) {
            $detalles[] = [
                'producto_id' => $prodId,
                'cantidad' => $_POST['cantidad'][$i]
            ];
        }
    }

    $controller->crear($data, $detalles);
    header("Location: listar.php?msg=Remito creado correctamente");
    exit;
}

$tipos = $controller->listarTipos();
$productos = $controller->listarProductos();
?>

<div class="container mt-4">
  <h2>➕ Nuevo Remito</h2>
  <form method="POST" class="card p-4 shadow-sm">

    <div class="mb-3">
      <label for="numero" class="form-label">Número</label>
      <input type="text" class="form-control" id="numero" name="numero" required>
    </div>

    <div class="mb-3">
      <label for="fecha" class="form-label">Fecha</label>
      <input type="date" class="form-control" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" required>
    </div>

    <div class="mb-3">
      <label for="tipo_remito_id" class="form-label">Tipo de Remito</label>
      <select name="tipo_remito_id" id="tipo_remito_id" class="form-select" required>
        <option value="">Seleccione...</option>
        <?php foreach ($tipos as $t): ?>
          <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label for="observaciones" class="form-label">Observaciones</label>
      <textarea class="form-control" name="observaciones" id="observaciones"></textarea>
    </div>

    <hr>
    <h4>📦 Detalle de Productos</h4>
    <div id="productos-container">
      <div class="row g-3 mb-2 producto-item">
        <div class="col-md-6">
          <select name="producto_id[]" class="form-select" required>
            <option value="">Seleccione producto</option>
            <?php foreach ($productos as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <input type="number" name="cantidad[]" class="form-control" placeholder="Cantidad" required>
        </div>
        <div class="col-md-2">
          <button type="button" class="btn btn-danger btn-remove">🗑</button>
        </div>
      </div>
    </div>

    <button type="button" id="add-producto" class="btn btn-secondary mb-3">➕ Agregar Producto</button>

    <div>
      <button type="submit" class="btn btn-primary">💾 Guardar</button>
      <a href="listar.php" class="btn btn-secondary">↩️ Cancelar</a>
    </div>
  </form>
</div>

<script>
document.getElementById("add-producto").addEventListener("click", function() {
  let container = document.getElementById("productos-container");
  let item = document.querySelector(".producto-item").cloneNode(true);
  item.querySelectorAll("input, select").forEach(el => el.value = "");
  container.appendChild(item);

  item.querySelector(".btn-remove").addEventListener("click", function() {
    item.remove();
  });
});

document.querySelectorAll(".btn-remove").forEach(btn => {
  btn.addEventListener("click", function() {
    btn.closest(".producto-item").remove();
  });
});
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
