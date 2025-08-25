<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ProductoController.php';
include_once __DIR__ . '/../../includes/header.php';

if ($_SESSION['rol'] != 'admin') die("Acceso denegado");

$controller = new ProductoController($conn);

$id = $_GET['id'] ?? 0;
$producto = $controller->obtener($id);
$categorias = $controller->listarCategorias();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre' => $_POST['nombre'],
        'categoria_id' => $_POST['categoria_id'],
        'unidad' => $_POST['unidad'],
        'atributos' => $_POST['atributos'] ?? []
    ];
    $controller->editar($id, $data);
    header("Location: listar.php?msg=Producto actualizado correctamente");
    exit;
}
?>

<div class="container mt-4">
  <h2>✏️ Editar Producto</h2>
  <form method="POST" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre</label>
      <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required>
    </div>

    <div class="mb-3">
      <label for="categoria_id" class="form-label">Categoría</label>
      <select class="form-select" id="categoria_id" name="categoria_id" required onchange="cargarAtributos(this.value)">
        <?php foreach ($categorias as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $c['id'] == $producto['categoria_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div id="atributos-container">
      <h5>Atributos</h5>
      <?php foreach ($controller->listarAtributosPorCategoria($producto['categoria_id']) as $attr): 
        $valor = "";
        foreach ($producto['atributos'] as $a) {
          if ($a['atributo_id'] == $attr['id']) $valor = $a['valor'];
        }
      ?>
        <div class="mb-3">
          <label class="form-label"><?= htmlspecialchars($attr['nombre']) ?></label>
          <input type="text" class="form-control" name="atributos[<?= $attr['id'] ?>]" value="<?= htmlspecialchars($valor) ?>" required>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mb-3">
      <label for="unidad" class="form-label">Unidad de Medida</label>
      <input type="text" class="form-control" id="unidad" name="unidad" value="<?= htmlspecialchars($producto['unidad']) ?>" required>
    </div>

    <div class="mb-3">
      <label for="stock" class="form-label">Stock</label>
      <input type="number" class="form-control" id="stock" value="<?= htmlspecialchars($producto['stock']) ?>" readonly>
    </div>

    <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
    <a href="listar.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<script>
function cargarAtributos(categoriaId) {
  fetch('obtener_atributos.php?categoria_id=' + categoriaId)
    .then(response => response.json())
    .then(data => {
      let html = '<h5>Atributos</h5>';
      data.forEach(attr => {
        html += `
          <div class="mb-3">
            <label class="form-label">${attr.nombre}</label>
            <input type="text" class="form-control" name="atributos[${attr.id}]" value="" required>
          </div>
        `;
      });
      document.getElementById('atributos-container').innerHTML = html;
    });
}
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
