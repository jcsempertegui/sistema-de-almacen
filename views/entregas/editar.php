<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/EntregaController.php';
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

  try {
      $controller->editar($id, $data, $detalles);
      header("Location: listar.php?msg=Entrega actualizada correctamente");
      exit;
  } catch (Exception $ex) {
      $error = $ex->getMessage(); // 🔴 Captura el error de stock o cualquier otro
  }
}
include_once __DIR__ . '/../../includes/header.php';

?>

<div class="container mt-4">
  <h2>✏ Editar Entrega</h2>
  <?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST" class="card p-4 shadow-sm" id="formEntrega">
    <div class="row mb-3">
      <div class="col-md-4">
        <label class="form-label">Trabajador</label>
        <select name="trabajador_id" class="form-select" required>
          <option value="">Seleccione...</option>
          <?php foreach ($trabajadores as $t): ?>
            <option value="<?= $t['id'] ?>" <?= (int)$entrega['trabajador_id'] === (int)$t['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($t['nombre'] . ' ' . $t['apellido_paterno'] . ' ' . $t['apellido_materno']) ?>
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
                    <?= htmlspecialchars($p['nombre']) ?>
                    <?= !empty($p['atributos']) ? ' — ' . htmlspecialchars($p['atributos']) : '' ?>
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

<!-- Select2 + JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Código JavaScript similar al de crear.php
document.addEventListener('DOMContentLoaded', function() {
    const addButton = document.getElementById('addRow');
    const tbody = document.getElementById('detalle-body');
    
    // Datos de productos desde PHP
    const productosData = <?= json_encode($productos) ?>;
    
    // Array para trackear productos seleccionados
    let productosSeleccionados = new Set();
    
    // Inicializar con los selects existentes
    const selectsExistentes = document.querySelectorAll('.producto-select');
    selectsExistentes.forEach(select => {
        if (select.value) {
            productosSeleccionados.add(select.value);
            select.setAttribute('data-previous-value', select.value);
        }
    });
    
    // Función para generar opciones de un select
    function generarOpcionesSelect(select, selectedValue = '') {
        // Limpiar opciones existentes (excepto la primera vacía)
        while (select.options.length > 1) {
            select.remove(1);
        }
        
        // Agregar opciones disponibles
        productosData.forEach(producto => {
            // Solo agregar opción si no está seleccionada o es la actualmente seleccionada
            if (!productosSeleccionados.has(producto.id.toString()) || producto.id.toString() === selectedValue) {
                const option = document.createElement('option');
                option.value = producto.id;
                option.textContent = producto.nombre + (producto.atributos ? " — " + producto.atributos : "");
                option.setAttribute('title', producto.atributos || '');
                
                if (producto.id.toString() === selectedValue) {
                    option.selected = true;
                }
                
                select.appendChild(option);
            }
        });
    }
    
    // Función para actualizar TODOS los selects
    function actualizarTodosLosSelects() {
        const todosSelects = document.querySelectorAll('.producto-select');
        
        todosSelects.forEach(select => {
            const currentValue = select.value;
            generarOpcionesSelect(select, currentValue);
            
            // Re-inicializar Select2
            $(select).select2({
                width: '100%',
                templateResult: function(state) {
                    if (!state.id) return state.text;
                    const attr = state.element.getAttribute('title');
                    if (attr) {
                        const dashIndex = state.text.indexOf(' — ');
                        const productName = dashIndex !== -1 ? state.text.substring(0, dashIndex) : state.text;
                        return productName + ' — ' + attr;
                    }
                    return state.text;
                },
                templateSelection: function(state) {
                    if (!state.id) return state.text;
                    const attr = state.element.getAttribute('title');
                    if (attr) {
                        const dashIndex = state.text.indexOf(' — ');
                        const productName = dashIndex !== -1 ? state.text.substring(0, dashIndex) : state.text;
                        return productName + ' — ' + attr;
                    }
                    return state.text;
                }
            });
        });
    }
    
    // Inicializar Select2 en todos los selects existentes
    $('.producto-select').select2({
        width: '100%',
        templateResult: function(state) {
            if (!state.id) return state.text;
            const attr = state.element.getAttribute('title');
            if (attr) {
                const dashIndex = state.text.indexOf(' — ');
                const productName = dashIndex !== -1 ? state.text.substring(0, dashIndex) : state.text;
                return productName + ' — ' + attr;
            }
            return state.text;
        },
        templateSelection: function(state) {
            if (!state.id) return state.text;
            const attr = state.element.getAttribute('title');
            if (attr) {
                const dashIndex = state.text.indexOf(' — ');
                const productName = dashIndex !== -1 ? state.text.substring(0, dashIndex) : state.text;
                return productName + ' — ' + attr;
            }
            return state.text;
        }
    }).on('change', function() {
        const oldValue = this.getAttribute('data-previous-value') || '';
        const newValue = this.value;
        
        // Actualizar tracking
        if (oldValue && oldValue !== '') {
            productosSeleccionados.delete(oldValue);
        }
        if (newValue && newValue !== '') {
            productosSeleccionados.add(newValue);
        }
        
        this.setAttribute('data-previous-value', newValue);
        actualizarTodosLosSelects();
    });
    
    // Inicializar todos los selects
    actualizarTodosLosSelects();
    
    // Agregar fila nueva
    addButton.addEventListener('click', function() {
        const newRow = tbody.querySelector('tr:first-child').cloneNode(true);
        const select = newRow.querySelector('select');
        const cantidadInput = newRow.querySelector('input[name="cantidad[]"]');
        const motivoInput = newRow.querySelector('input[name="motivo[]"]');
        
        // Limpiar valores
        select.value = '';
        cantidadInput.value = '1';
        motivoInput.value = '';
        
        // Remover Select2 si existe
        $(select).removeClass('select2-hidden-accessible');
        $(select).siblings('.select2-container').remove();
        
        // Restaurar clases Bootstrap
        select.class