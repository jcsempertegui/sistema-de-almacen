<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/RemitoController.php';
include_once __DIR__ . '/../../includes/header.php';

if ($_SESSION['rol'] !== 'admin') { die('Acceso denegado'); }

$controller = new RemitoController($conn);
$tipos     = $controller->listarTipos();
$productos = $controller->listarProductos();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'usuario_id'      => $_SESSION['user_id'],
        'tipo_remito_id'  => (int)$_POST['tipo_remito_id'],
        'fecha'           => $_POST['fecha'],
        'señores'         => $_POST['señores'] ?? '',
        'atencion'        => $_POST['atencion'] ?? '',
        'contrato'        => $_POST['contrato'] ?? '',
        'numero_remito'   => $_POST['numero_remito'] ?? '',
        'campo'           => $_POST['campo'] ?? '',
        'orden'           => $_POST['orden'] ?? '',
        'observaciones'   => $_POST['observaciones'] ?? '',
        'despachado'      => $_POST['despachado'] ?? '',
        'transportado'    => $_POST['transportado'] ?? '',
        'placa'           => $_POST['placa'] ?? '',
        'recibido'        => $_POST['recibido'] ?? ''
    ];

    // Detalles
    $detalles = [];
    if (!empty($_POST['producto_id'])) {
        foreach ($_POST['producto_id'] as $i => $pid) {
            $pid = (int)$pid;
            $cant = (int)($_POST['cantidad'][$i] ?? 0);
            if ($pid > 0 && $cant > 0) $detalles[] = ['producto_id' => $pid, 'cantidad' => $cant];
        }
    }

    $controller->crear($data, $detalles);
    header("Location: listar.php?msg=Remito creado");
    exit;
}
?>
<div class="container mt-3">
  <h2>➕ Nuevo Remito</h2>

  <form method="post" class="card p-3 shadow-sm">
    <div class="row g-3">
      <div class="col-md-2">
        <label class="form-label">Fecha</label>
        <input type="date" name="fecha" class="form-control" required value="<?= date('Y-m-d') ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Tipo</label>
        <select name="tipo_remito_id" class="form-select" required>
          <?php foreach ($tipos as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">N° Remito</label>
        <input type="text" name="numero_remito" class="form-control">
      </div>
      <div class="col-md-5">
        <label class="form-label">Campo</label>
        <input type="text" name="campo" class="form-control">
      </div>

      <div class="col-md-6">
        <label class="form-label">Señores</label>
        <input type="text" name="señores" class="form-control">
      </div>
      <div class="col-md-6">
        <label class="form-label">Atención</label>
        <input type="text" name="atencion" class="form-control">
      </div>

      <div class="col-md-4">
        <label class="form-label">Contrato</label>
        <input type="text" name="contrato" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Orden</label>
        <input type="text" name="orden" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Placa</label>
        <input type="text" name="placa" class="form-control">
      </div>

      <div class="col-md-4">
        <label class="form-label">Despachado</label>
        <input type="text" name="despachado" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Transportado</label>
        <input type="text" name="transportado" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Recibido</label>
        <input type="text" name="recibido" class="form-control">
      </div>

      <div class="col-12">
        <label class="form-label">Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"></textarea>
      </div>
    </div>

    <hr>

    <h5>Detalle</h5>
    <div id="detalle-rows" class="vstack gap-2"></div>
    <button type="button" class="btn btn-outline-primary mt-2" id="btnAddRow">➕ Agregar producto</button>

    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">💾 Guardar</button>
      <a class="btn btn-secondary" href="listar.php">Cancelar</a>
    </div>
  </form>
</div>

<script src="<?= BASE_URL ?>/public/js/remitos.js"></script>
<script>
  window.__PRODUCTOS__ = <?= json_encode($productos, JSON_UNESCAPED_UNICODE) ?>;
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
