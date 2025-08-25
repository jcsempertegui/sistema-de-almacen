<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ProductoController.php';
include_once __DIR__ . '/../../includes/header.php';

if ($_SESSION['rol'] != 'admin') die("Acceso denegado");

$controller = new ProductoController($conn);
$categorias = $controller->listarCategorias();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = [
      'nombre'       => $_POST['nombre'],
      'categoria_id' => $_POST['categoria_id'],
      'unidad'       => $_POST['unidad'],
      'stock'        => 0
  ];

  // Supongamos que los atributos llegan como arrays en el POST
  $atributos = [];
  if (isset($_POST['atributo_id'])) {
      foreach ($_POST['atributo_id'] as $i => $attrId) {
          $atributos[] = [
              'atributo_id' => $attrId,
              'valor'       => $_POST['valor'][$i]
          ];
      }
  }

  $resultado = $controller->crear($data, $atributos);

  if ($resultado === "duplicado") {
      $error = "⚠️ Ya existe un producto con el mismo nombre y atributos.";
  } else {
      header("Location: listar.php?msg=Producto creado correctamente");
      exit;
  }
}
?>
<?php if (isset($error)): ?>
  <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="container mt-4">
  <h2>➕ Nuevo Producto</h2>
  <form method="POST" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre</label>
      <input type="text" class="form-control" id="nombre" name="nombre" required>
    </div>

    <div class="mb-3">
      <label for="categoria_id" class="form-label">Categoría</label>
      <select class="form-select" id="categoria_id" name="categoria_id" required onchange="cargarAtributos(this.value)">
        <option value="">Seleccione una categoría</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div id="atributos-container"></div>

    <div class="mb-3">
      <label for="unidad" class="form-label">Unidad de Medida</label>
      <input type="text" class="form-control" id="unidad" name="unidad" required>
    </div>

    <div class="mb-3">
      <label for="stock" class="form-label">Stock Inicial</label>
      <input type="number" class="form-control" id="stock" name="stock" value="0" readonly>
    </div>

    <button type="submit" class="btn btn-primary">💾 Guardar</button>
    <a href="listar.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<script>
function cargarAtributos(categoriaId) {
  if (!categoriaId) {
    document.getElementById('atributos-container').innerHTML = '';
    return;
  }
  fetch('obtener_atributos.php?categoria_id=' + categoriaId)
    .then(response => response.json())
    .then(data => {
      let html = '<h5>Atributos</h5>';
      data.forEach(attr => {
        html += `
          <div class="mb-3">
            <label class="form-label">${attr.nombre}</label>
            <input type="text" class="form-control" name="atributos[${attr.id}]" required>
          </div>
        `;
      });
      document.getElementById('atributos-container').innerHTML = html;
    });
}
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
