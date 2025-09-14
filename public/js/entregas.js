// public/js/entregas.js
(function ($) {
  'use strict';

  $(function() {
      const $tbody = $('#detalle-body');
      const $add   = $('#addRow');

      // Guardar lista maestra desde el primer <select>
      let masterOptions = [];
      $('.producto-select:first option').each(function() {
          masterOptions.push({
              value: $(this).val(),
              text: $(this).text(),
              title: $(this).attr('title') || ''
          });
      });

      // Formato para Select2
      function formatProduct(state) {
          if (!state.id) return state.text;
          const $option = $(state.element);
          const attr = $option.attr('title');
          return attr ? state.text + ' — ' + attr : state.text;
      }

      // Inicializar Select2 en un select
      function initSelect2($sel) {
          if ($sel.hasClass('select2-hidden-accessible')) {
              $sel.select2('destroy');
          }
          $sel.select2({
              width: '100%',
              templateResult: formatProduct,
              templateSelection: formatProduct
          }).off('change.unique').on('change.unique', actualizarOpciones);
      }

      // Actualizar todas las listas evitando repetidos
      function actualizarOpciones() {
          const usados = $('.producto-select').map(function() {
              return $(this).val();
          }).get().filter(v => v !== "");

          $('.producto-select').each(function() {
              const $sel = $(this);
              const actual = $sel.val();

              let html = '<option value="">Seleccione...</option>';
              masterOptions.forEach(o => {
                  if (o.value === "") return;
                  // Mostrar solo si no está usado o es el seleccionado actual
                  if (actual === o.value || !usados.includes(o.value)) {
                      html += `<option value="${o.value}" title="${o.title}">${o.text}</option>`;
                  }
              });

              $sel.html(html).val(actual);
              initSelect2($sel);
          });
      }

      // Nueva fila
      function newRow() {
          let html = '<option value="">Seleccione...</option>';
          masterOptions.forEach(o => {
              if (o.value !== "") {
                  html += `<option value="${o.value}" title="${o.title}">${o.text}</option>`;
              }
          });
          return `
              <tr class="detalle-row">
                <td>
                  <select name="producto_id[]" class="form-select producto-select" required>
                    ${html}
                  </select>
                </td>
                <td><input type="number" min="1" name="cantidad[]" class="form-control" value="1" required></td>
                <td><input type="text" name="motivo[]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger btn-sm removeRow">🗑</button></td>
              </tr>
          `;
      }

      // Agregar fila
      $add.on('click', function() {
          const $row = $(newRow());
          $tbody.append($row);
          initSelect2($row.find('.producto-select'));
          actualizarOpciones();
      });

      // Eliminar fila
      $tbody.on('click', '.removeRow', function() {
          $(this).closest('tr').remove();
          actualizarOpciones();
      });

      // Inicialización
      initSelect2($('.producto-select'));
      actualizarOpciones();
  });
})(jQuery);
