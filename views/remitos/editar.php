<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/RemitoController.php';
include_once __DIR__ . '/../../includes/header.php';

if ($_SESSION['rol'] != 'admin') {
    die("Acceso denegado");
}

$controller = new RemitoController($conn);
$id = $_GET['id'] ?? null;
$remito = $controller->obtener($id);

$tipos = $controller->listarTipos();
$productos = $controller->listarProductos();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'tipo_remito_id' => $_POST['tipo_remito_id'],
        'numero'         => $_POST['numero'],
        'señores'        => $_POST['señores'],
        'atencion'       => $_POST['atencion'],
        'contrato'       => $_POST['contrato'],
        'campo'          => $_POST['campo'],
        'orden'          => $_POST['orden'],
        'observaciones'  => $_POST['observaciones'],
        'despachado'     => $_POST['despachado'],
        'transportado'   => $_POST['transportado'],
        'placa'          => $_POST['placa'],
        'recibido'       => $_POST['recibido'],
    ];

    $detalles = [];
    foreach ($_POST['producto_id'] as $i => $productoId) {
        $detalles[] = [
            'producto_id' => $productoId,
            'cantidad'    => $_POST['cantidad'][$i],
        ];
    }

    $controller->editar($id, $data, $detalles);
    header("Location: listar.php?msg=Remito actualizado correctamente");
    exit;
}
?>

<div class="container mt-4">
  <h2>✏ Editar Remito</h2>

  <form method="POST" class="card p-4 shadow-sm">
    <div class="row mb-3">
      <div class="col-md-4">
        <label class="form-label">Número</label>
        <input type="text" name="numero" class="form-control"
               value="<?= htmlspecialchars($remito['numero']) ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Tipo</label>
        <select name="tipo_remito_id" class="form-select" required>
          <?php foreach ($tipos as $t): ?>
            <option value="<?= $t['id'] ?>" <?= $remito['tipo_remito_id'] == $t['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($t['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Resto de campos -->
    <div class="mb-3"><label class="form-label">Señores</label>
      <input type="text" name="señores" class="form-control" value="<?= htmlspecialchars($remito['señores'] ?? '') ?>">
    </div>
    <div class="mb-3"><label class="form-label">Atención</label>
      <input type="text" name="atencion" class="form-control" value="<?= htmlspecialchars($remito['atencion'] ?? '') ?>">
    </div>
    <div class="mb-3"><label class="form-label">Contrato</label>
      <input type="text" name="contrato" class="form-control" value="<?= htmlspecialchars($remito['contrato'] ?? '') ?>">
    </div>
    <div class="mb-3"><label class="form-label">Campo</label>
      <input type="text" name="campo" class="form-control" value="<?= htmlspecialchars($remito['campo'] ?? '') ?>">
    </div>
    <div class="mb-3"><label class="form-label">Orden</label>
      <input type="text" name="orden" class="form-control" value="<?= htmlspecialchars($remito['orden'] ?? '') ?>">
    </div>
    <div class="mb-3"><label class="form-label">Observaciones</label>
      <textarea name="observaciones" class="form-control"><?= htmlspecialchars($remito['observaciones'] ?? '') ?></textarea>
    </div>
    <div class="mb-3"><label class="form-label">Despachado</label>
      <input type="text" name="despachado" class="form-control" value="<?= htmlspecialchars($remito['despachado'] ?? '') ?>">
    </div>
    <div class="mb-3"><label class="form-label">Transportado</label>
      <input type="text" name="transportado" class="form-control" value="<?= htmlspecialchars($remito['transportado'] ?? '') ?>">
    </div>
    <div class="mb-3"><label class="form-label">Placa</label>
      <input type="text" name="placa" class="form-control" value="<?= htmlspecialchars($remito['placa'] ?? '') ?>">
    </div>
    <div class="mb-3"><label class="form-label">Recibido</label>
      <input type="text" name="recibido" class="form-control" value="<?= htmlspecialchars($remito['recibido'] ?? '') ?>">
    </div>

    <!-- Productos -->
    <h5>🛒 Detalle de Productos</h5>
    <table class="table table-bordered" id="productosTable">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Cantidad</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($remito['detalles'] as $d): ?>
          <tr>
            <td>
              <select name="producto_id[]" class="form-select producto-select" required>
                <?php foreach ($productos as $p): ?>
                  <option value="<?= $p['id'] ?>"
                          title="<?= htmlspecialchars($p['atributos']) ?>"
                          <?= $p['id'] == $d['producto_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nombre']) ?> <?= $p['atributos'] ? " (" . htmlspecialchars($p['atributos']) . ")" : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
            <td><input type="number" name="cantidad[]" class="form-control" value="<?= $d['cantidad'] ?>" required></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">🗑</button></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <button type="button" class="btn btn-secondary mb-3" id="addRow">➕ Agregar Producto</button>

    <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
    <a href="listar.php" class="btn btn-secondary">↩️ Cancelar</a>
  </form>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
  $('.producto-select').select2({
    width: '100%',
    templateResult: formatProduct,
    templateSelection: formatProduct
  });
});
function formatProduct(state) {
  if (!state.id) return state.text;
  const attr = $(state.element).attr('title');
  if (attr) return $('<span>').html(state.text + ' <small style="color:#555;">(' + attr + ')</small>');
  return state.text;
}
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
