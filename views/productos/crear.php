<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ProductoController.php';
include_once __DIR__ . '/../../includes/header.php';

if ($_SESSION['rol'] != 'admin') {
    die("Acceso denegado");
}

$controller = new ProductoController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre'       => $_POST['nombre'],
        'categoria_id' => intval($_POST['categoria_id']),
        'unidad'       => $_POST['unidad'],
        'stock'        => 0
    ];

    // Construir array de atributos a partir del POST
    $atributos = [];
    if (isset($_POST['atributos']) && is_array($_POST['atributos'])) {
        foreach ($_POST['atributos'] as $atributo_id => $valor) {
            if ($valor === '' || $valor === null) continue;
            $atributos[] = [
                'atributo_id' => intval($atributo_id),
                'valor'       => trim($valor)
            ];
        }
    }

    if ($controller->crear($data, $atributos)) {
        header("Location: listar.php?msg=Producto creado correctamente");
        exit;
    } else {
        header("Location: listar.php?msg=⚠️ Ya existe un producto con el mismo nombre y atributos (o intentaste duplicar un atributo)");
        exit;
    }
}

// Traer categorías para el select
$categorias = $controller->listarCategorias();
?>

<div class="container">
  <h2 class="mb-4">➕ Nuevo Producto</h2>

  <form method="POST" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre del Producto</label>
      <input type="text" class="form-control" id="nombre" name="nombre" required>
    </div>

    <div class="mb-3">
      <label for="categoria_id" class="form-label">Categoría</label>
      <select class="form-select" id="categoria_id" name="categoria_id" required>
        <option value="">Seleccione una categoría</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="form-text">Al seleccionar una categoría se cargarán sus atributos.</div>
    </div>

    <!-- Aquí se insertan dinámicamente los inputs de atributos -->
    <div id="atributos-container"></div>

    <div class="mb-3">
      <label for="unidad" class="form-label">Unidad de Medida</label>
      <input type="text" class="form-control" id="unidad" name="unidad" placeholder="Ej: Caja, Unidad, Litro" required>
    </div>

    <div class="mb-3">
      <label for="stock" class="form-label">Stock Inicial</label>
      <input type="number" class="form-control" id="stock" name="stock" value="0" readonly>
      <div class="form-text text-muted">El stock inicial siempre es 0; se actualizará con remitos de entrada/salida.</div>
    </div>

    <button type="submit" class="btn btn-primary">💾 Guardar</button>
    <a href="listar.php" class="btn btn-secondary">↩️ Cancelar</a>
  </form>
</div>

<script>
  // Usamos BASE_URL del header
  const BASE_URL = "<?= BASE_URL ?>";
  const selectCategoria = document.getElementById('categoria_id');
  const contenedorAtributos = document.getElementById('atributos-container');

  selectCategoria.addEventListener('change', function () {
    const categoriaId = this.value;
    if (!categoriaId) {
      contenedorAtributos.innerHTML = '';
      return;
    }
    fetch(`${BASE_URL}/public/api/atributos.php?categoria_id=${categoriaId}`)
      .then(res => res.json())
      .then(data => {
        if (!Array.isArray(data) || data.length === 0) {
          contenedorAtributos.innerHTML = '<div class="alert alert-secondary">Esta categoría no tiene atributos configurados.</div>';
          return;
        }
        let html = '<h5 class="mt-3">Atributos</h5>';
        data.forEach(attr => {
          html += `
            <div class="mb-3">
              <label class="form-label">${attr.nombre}</label>
              <input type="text" class="form-control" name="atributos[${attr.id}]" placeholder="Ingrese ${attr.nombre}">
            </div>
          `;
        });
        contenedorAtributos.innerHTML = html;
      })
      .catch(() => {
        contenedorAtributos.innerHTML = '<div class="alert alert-danger">No se pudieron cargar los atributos.</div>';
      });
  });
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
