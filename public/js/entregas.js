// public/js/entregas.js
// Requiere jQuery y Select2 en la página
(function () {
    const $tbody = $('#detalle-body');
    const $add   = $('#addRow');
  
    function initSelect2(ctx) {
      (ctx || $(document)).find('.producto-select').select2({
        width: '100%',
        templateResult: formatProduct,
        templateSelection: formatProduct
      });
    }
  
    function formatProduct(state) {
      if (!state.id) return state.text;
      const attr = $(state.element).attr('title');
      if (attr) return $('<span>').html(state.text + ' <small style="color:#555;">— ' + attr + '</small>');
      return state.text;
    }
  
    function newRowHtml() {
      const $firstSelect = $('#detalle-body .producto-select:first');
      const optionsHtml = $firstSelect.length ? $firstSelect.html() : '<option value="">Seleccione...</option>';
      return `
        <tr class="detalle-row">
          <td>
            <select name="producto_id[]" class="form-select producto-select" required>
              ${optionsHtml}
            </select>
          </td>
          <td><input type="number" min="1" name="cantidad[]" class="form-control" value="1" required></td>
          <td><input type="text" name="motivo[]" class="form-control"></td>
          <td><button type="button" class="btn btn-danger btn-sm removeRow">🗑</button></td>
        </tr>
      `;
    }
  
    $add.on('click', function () {
      const $row = $(newRowHtml());
      $tbody.append($row);
      initSelect2($row);
    });
  
    $tbody.on('click', '.removeRow', function () {
      const $rows = $tbody.find('.detalle-row');
      if ($rows.length === 1) {
        // resetear última fila
        $rows.find('select').val('').trigger('change');
        $rows.find('input[name="cantidad[]"]').val(1);
        $rows.find('input[name="motivo[]"]').val('');
      } else {
        $(this).closest('tr').remove();
      }
    });
  
    // inicializar selects existentes
    $(document).ready(function() {
      initSelect2($(document));
    });
  })();
  