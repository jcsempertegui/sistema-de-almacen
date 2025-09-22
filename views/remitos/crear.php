<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/RemitoController.php';

$controller = new RemitoController($conn);

// ✅ Cargar datos para los selects
$tiposRemito = $controller->listarTipos();
$productos   = $controller->listarProductos();
$usuarios    = $controller->listarUsuarios();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'tipo_remito_id' => $_POST['tipo_remito_id'],
        'usuario_id'     => $_SESSION['user_id'],
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
        'recibido'       => $_POST['recibido']
    ];

    $detalles = [];
    if (!empty($_POST['producto_id']) && is_array($_POST['producto_id'])) {
        foreach ($_POST['producto_id'] as $i => $pid) {
            if ($pid === '' || !isset($_POST['cantidad'][$i])) continue;
            $detalles[] = [
                'producto_id' => (int)$pid,
                'cantidad'    => (int)$_POST['cantidad'][$i]
            ];
        }
    }

    if (empty($detalles)) {
        $error = "Debe agregar al menos un producto.";
    } else {
        try {
            $controller->crear($data, $detalles);
            header("Location: listar.php?msg=Remito creado correctamente");
            exit;
        } catch (Exception $ex) {
            $error = $ex->getMessage();
        }
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>
<div class="container mt-4">
  <h2>➕ Nuevo Remito</h2>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" class="card p-4 shadow-sm">
    <div class="row mb-3">
      <div class="col-md-4">
        <label for="tipo_remito_id" class="form-label">Tipo de Remito</label>
        <select name="tipo_remito_id" id="tipo_remito_id" class="form-select" required>
          <option value="">Seleccionar...</option>
          <?php foreach ($tiposRemito as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Número</label>
        <input type="text" name="numero" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Contrato</label>
        <input type="text" name="contrato" class="form-control">
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <label class="form-label">Señores</label>
        <input type="text" name="señores" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Atención</label>
        <input type="text" name="atencion" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Campo</label>
        <input type="text" name="campo" class="form-control">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Orden</label>
      <input type="text" name="orden" class="form-control">
    </div>

    <div class="mb-3">
      <label class="form-label">Observaciones</label>
      <textarea name="observaciones" class="form-control"></textarea>
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <label class="form-label">Despachado por</label>
        <input type="text" name="despachado" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Transportado por</label>
        <input type="text" name="transportado" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Placa</label>
        <input type="text" name="placa" class="form-control">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Recibido por</label>
      <input type="text" name="recibido" class="form-control">
    </div>

    <h5 class="mt-3">🛒 Detalle de Productos</h5>
    <table class="table table-bordered" id="productosTable">
      <thead>
        <tr>
          <th style="width:70%">Producto</th>
          <th style="width:20%">Cantidad</th>
          <th style="width:10%">Acción</th>
        </tr>
      </thead>
      <tbody id="detalle-body">
        <tr class="detalle-row">
          <td>
            <select name="producto_id[]" class="form-select producto-select" required>
              <option value="">Seleccione...</option>
              <?php foreach ($productos as $p): ?>
                <option value="<?= $p['id'] ?>" title="<?= htmlspecialchars($p['atributos'] ?? '') ?>">
                  <?= htmlspecialchars($p['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </td>
          <td><input type="number" min="1" name="cantidad[]" class="form-control" value="1" required></td>
          <td><button type="button" class="btn btn-danger btn-sm removeRow">🗑</button></td>
        </tr>
      </tbody>
    </table>

    <button type="button" class="btn btn-secondary mb-3" id="addRow">➕ Agregar Producto</button>

    <div>
      <button type="submit" class="btn btn-primary">💾 Guardar</button>
      <a href="listar.php" class="btn btn-secondary">↩ Cancelar</a>
    </div>
  </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?= BASE_URL ?>/public/js/remitos.js"></script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
