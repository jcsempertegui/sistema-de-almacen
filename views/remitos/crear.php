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
$tipos = $controller->listarTipos();
$productos = $controller->listarProductos();
$usuarios = $controller->listarUsuarios();

// Guardar remito
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
        'recibido'       => $_POST['recibido'],
    ];

    $detalles = [];
    foreach ($_POST['producto_id'] as $i => $productoId) {
        $detalles[] = [
            'producto_id' => $productoId,
            'cantidad'    => $_POST['cantidad'][$i],
        ];
    }

    if ($controller->crear($data, $detalles)) {
        header("Location: listar.php?msg=Remito creado correctamente");
        exit;
    } else {
        $error = "Error al crear remito.";
    }
}
?>

<div class="container mt-4">
  <h2>➕ Nuevo Remito</h2>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" class="card p-4 shadow-sm" id="form-remito">
    <div class="row mb-3">
      <div class="col-md-4">
        <label for="numero" class="form-label">Número</label>
        <input type="text" class="form-control" id="numero" name="numero" required>
      </div>
      <div class="col-md-4">
        <label for="tipo_remito_id" class="form-label">Tipo</label>
        <select name="tipo_remito_id" id="tipo_remito_id" class="form-select" required>
          <option value="">Seleccione...</option>
          <?php foreach ($tipos as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Datos adicionales -->
    <div class="mb-3"><label class="form-label">Señores</label><input type="text" name="señores" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Atención</label><input type="text" name="atencion" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Contrato</label><input type="text" name="contrato" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Campo</label><input type="text" name="campo" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Orden</label><input type="text" name="orden" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Observaciones</label><textarea name="observaciones" class="form-control"></textarea></div>
    <div class="mb-3"><label class="form-label">Despachado</label><input type="text" name="despachado" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Transportado</label><input type="text" name="transportado" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Placa</label><input type="text" name="placa" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Recibido</label><input type="text" name="recibido" class="form-control"></div>

    <!-- Productos dinámicos -->
    <h5>🛒 Detalle de Productos</h5>
    <table class="table table-bordered" id="productosTable">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Cantidad</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody id="detalle-body">
        <tr class="detalle-row">
          <td>
            <select name="producto_id[]" class="form-select producto-select" required>
              <option value="">Seleccione...</option>
              <?php foreach ($productos as $p): ?>
                <option value="<?= $p['id'] ?>"
                        title="<?= htmlspecialchars($p['atributos']) ?>">
                  <?= htmlspecialchars($p['nombre']) ?>
                  <?= $p['atributos'] ? " (" . htmlspecialchars($p['atributos']) . ")" : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </td>
          <td><input type="number" name="cantidad[]" class="form-control" min="1" value="1" required></td>
          <td><button type="button" class="btn btn-danger btn-sm removeRow">🗑</button></td>
        </tr>
      </tbody>
    </table>
    <button type="button" class="btn btn-secondary mb-3" id="addRow">➕ Agregar Producto</button>

    <button type="submit" class="btn btn-primary">💾 Guardar</button>
    <a href="listar.php" class="btn btn-secondary">↩️ Cancelar</a>
  </form>
</div>

<!-- Select2 + JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// SOLUCIÓN COMPLETAMENTE NUEVA - GENERACIÓN DINÁMICA DE OPCIONES
document.addEventListener('DOMContentLoaded', function() {
    const addButton = document.getElementById('addRow');
    const tbody = document.getElementById('detalle-body');
    
    // Datos de productos desde PHP
    const productosData = <?= json_encode($productos) ?>;
    
    // Array para trackear productos seleccionados
    let productosSeleccionados = new Set();
    
    // Inicializar con el primer select si tiene valor
    const primerSelect = document.querySelector('.producto-select');
    if (primerSelect && primerSelect.value) {
        productosSeleccionados.add(primerSelect.value);
    }
    
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
                option.textContent = producto.nombre + (producto.atributos ? " (" + producto.atributos + ")" : "");
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
        });
    }
    
    // Inicializar Select2 solo en el select inicial
    const selectInicial = $('.producto-select').first();
    selectInicial.select2({
        width: '100%',
        templateResult: function(state) {
            if (!state.id) return state.text;
            const attr = state.element.getAttribute('title');
            const originalText = state.text;
            if (attr) {
                const parenIndex = originalText.indexOf(' (');
                const productName = parenIndex !== -1 ? originalText.substring(0, parenIndex) : originalText;
                return productName + ' (' + attr + ')';
            }
            return originalText;
        },
        templateSelection: function(state) {
            if (!state.id) return state.text;
            const attr = state.element.getAttribute('title');
            const originalText = state.text;
            if (attr) {
                const parenIndex = originalText.indexOf(' (');
                const productName = parenIndex !== -1 ? originalText.substring(0, parenIndex) : originalText;
                return productName + ' (' + attr + ')';
            }
            return originalText;
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
    
    // Inicializar el primer select
    generarOpcionesSelect(primerSelect, primerSelect.value);
    if (primerSelect.value) {
        primerSelect.setAttribute('data-previous-value', primerSelect.value);
    }
    
    // Agregar fila nueva
    addButton.addEventListener('click', function() {
        const newRow = tbody.querySelector('tr:first-child').cloneNode(true);
        const select = newRow.querySelector('select');
        const input = newRow.querySelector('input');
        
        // Limpiar valores
        select.value = '';
        input.value = '1';
        
        // Remover Select2 si existe
        $(select).removeClass('select2-hidden-accessible');
        $(select).siblings('.select2-container').remove();
        
        // Restaurar clases Bootstrap
        select.className = 'form-select producto-select';
        
        // Generar opciones para el nuevo select
        generarOpcionesSelect(select);
        
        // Agregar evento change
        select.addEventListener('change', function() {
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
        
        tbody.appendChild(newRow);
    });
    
    // Eliminar fila
    tbody.addEventListener('click', function(e) {
        if (e.target.classList.contains('removeRow')) {
            const rows = tbody.querySelectorAll('tr');
            if (rows.length > 1) {
                const row = e.target.closest('tr');
                const select = row.querySelector('.producto-select');
                const selectedValue = select.value;
                
                // Remover del tracking
                if (selectedValue) {
                    productosSeleccionados.delete(selectedValue);
                }
                
                row.remove();
                actualizarTodosLosSelects();
            } else {
                alert('Debe haber al menos un producto en el remito.');
            }
        }
    });
    
    // Validación del formulario
    document.getElementById('form-remito').addEventListener('submit', function(e) {
        const selects = document.querySelectorAll('.producto-select');
        const selectedProducts = new Set();
        let hasDuplicates = false;
        let emptyFields = false;
        
        selects.forEach(select => {
            select.style.borderColor = '';
            
            if (!select.value) {
                emptyFields = true;
                select.style.borderColor = 'red';
            } else if (selectedProducts.has(select.value)) {
                hasDuplicates = true;
                select.style.borderColor = 'red';
            } else {
                selectedProducts.add(select.value);
            }
        });
        
        if (emptyFields) {
            e.preventDefault();
            alert('ERROR: Todos los productos deben ser seleccionados.');
            return;
        }
        
        if (hasDuplicates) {
            e.preventDefault();
            alert('ERROR: No puede haber productos duplicados en el remito.');
        }
    });
});
</script>

<style>
/* Estilos para uniformizar la apariencia */
#productosTable {
    margin-bottom: 0;
}

#productosTable th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.detalle-row td {
    vertical-align: middle !important;
    padding: 0.75rem;
}

.detalle-row .form-control,
.detalle-row .form-select {
    height: 38px;
    min-height: 38px;
    width: 100%;
}

.select2-container .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}

#addRow {
    margin-top: 1rem;
}

.removeRow {
    width: 100%;
    padding: 0.25rem 0.5rem;
    white-space: nowrap;
}

/* Estilo para selects con error */
.form-select[style*="border-color: red"] {
    border-color: red !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

/* Asegurar que las celdas tengan el mismo ancho */
#productosTable td:nth-child(1) { width: 60%; }
#productosTable td:nth-child(2) { width: 20%; }
#productosTable td:nth-child(3) { width: 20%; }
</style>
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>