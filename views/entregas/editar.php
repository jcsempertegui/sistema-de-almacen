<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/EntregaController.php';
include_once __DIR__ . '/../../includes/header.php';

if ($_SESSION['rol'] != 'admin') die("Acceso denegado");

$controller = new EntregaController($conn);
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$entrega = $controller->obtener($id);
$productos = $controller->listarProductos();
$trabajadores = $controller->listarTrabajadores();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'trabajador_id' => $_POST['trabajador_id'],
        'fecha'         => $_POST['fecha'],
        'campo'         => $_POST['campo'] ?? '',
        'inspector'     => $_POST['inspector'] ?? ''
    ];

    $detalles = [];
    if (!empty($_POST['producto_id']) && is_array($_POST['producto_id'])) {
        foreach ($_POST['producto_id'] as $i => $pid) {
            if ($pid === '' || !isset($_POST['cantidad'][$i])) continue;
            $detalles[] = [
                'producto_id' => (int)$pid,
                'cantidad' => (int)$_POST['cantidad'][$i],
                'motivo' => $_POST['motivo'][$i] ?? ''
            ];
        }
    }

    $controller->editar($id, $data, $detalles);
    header("Location: listar.php?msg=Entrega actualizada correctamente");
    exit;
}
?>

<div class="container mt-4">
  <h2>✏ Editar Entrega</h2>

  <form method="POST" class="card p-4 shadow-sm" id="formEntrega">
    <div class="row mb-3">
      <div class="col-md-4">
        <label class="form-label">Trabajador</label>
        <select name="trabajador_id" class="form-select" required>
          <option value="">Seleccione...</option>
          <?php foreach ($trabajadores as $t): ?>
            <option value="<?= $t['id'] ?>" <?= (int)$entrega['trabajador_id'] === (int)$t['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($t['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Fecha</label>
        <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($entrega['fecha'] ?? date('Y-m-d')) ?>" required>
      </div>
      <div class="col-md-5">
        <label class="form-label">Inspector</label>
        <input type="text" name="inspector" class="form-control" value="<?= htmlspecialchars($entrega['inspector'] ?? '') ?>">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Campo</label>
      <input type="text" name="campo" class="form-control" value="<?= htmlspecialchars($entrega['campo'] ?? '') ?>">
    </div>

    <h5 class="mt-3">🛒 Detalle de Productos</h5>
    <table class="table table-bordered" id="productosTable">
      <thead>
        <tr>
          <th style="width:55%">Producto</th>
          <th style="width:15%">Cantidad</th>
          <th style="width:20%">Motivo</th>
          <th style="width:10%">Acción</th>
        </tr>
      </thead>
      <tbody id="detalle-body">
        <?php foreach ($entrega['detalles'] as $d): ?>
          <tr class="detalle-row">
            <td>
              <select name="producto_id[]" class="form-select producto-select" required>
                <option value="">Seleccione...</option>
                <?php foreach ($productos as $p): ?>
                  <option value="<?= $p['id'] ?>"
                          title="<?= htmlspecialchars($p['atributos'] ?? '') ?>"
                          <?= (int)$p['id'] === (int)$d['producto_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nombre']) ?><?= !empty($p['atributos']) ? ' — '.htmlspecialchars($p['atributos']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
            <td><input type="number" min="1" name="cantidad[]" class="form-control" value="<?= (int)$d['cantidad'] ?>" required></td>
            <td><input type="text" name="motivo[]" class="form-control" value="<?= htmlspecialchars($d['motivo'] ?? '') ?>"></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">🗑</button></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <button type="button" class="btn btn-secondary mb-3" id="addRow">➕ Agregar Producto</button>

    <div>
      <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
      <a href="listar.php" class="btn btn-secondary">↩ Cancelar</a>
    </div>
  </form>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?= BASE_URL ?>/public/js/entregas.js"></script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
