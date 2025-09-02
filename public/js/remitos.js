(function () {
    const container = document.getElementById('detalle-rows');
    const btnAdd = document.getElementById('btnAddRow');
  
    if (!container || !btnAdd) return;
  
    function buildRow(values) {
      const row = document.createElement('div');
      row.className = 'row g-2 align-items-end border p-2 rounded';
  
      // Producto select
      const colProd = document.createElement('div');
      colProd.className = 'col-md-6';
      const sel = document.createElement('select');
      sel.name = 'producto_id[]';
      sel.className = 'form-select';
      sel.required = true;
  
      const opt0 = document.createElement('option');
      opt0.value = '';
      opt0.textContent = 'Seleccione producto';
      sel.appendChild(opt0);
  
      (window.__PRODUCTOS__ || []).forEach(p => {
        const o = document.createElement('option');
        o.value = p.id;
        o.textContent = `${p.nombre} (${p.unidad})`;
        if (values && +values.producto_id === +p.id) o.selected = true;
        sel.appendChild(o);
      });
  
      colProd.appendChild(labelWrap('Producto', sel));
  
      // Cantidad
      const colCant = document.createElement('div');
      colCant.className = 'col-md-3';
      const inp = document.createElement('input');
      inp.type = 'number';
      inp.name = 'cantidad[]';
      inp.min = '1';
      inp.required = true;
      inp.className = 'form-control';
      if (values && values.cantidad) inp.value = values.cantidad;
      colCant.appendChild(labelWrap('Cantidad', inp));
  
      // Botón eliminar
      const colDel = document.createElement('div');
      colDel.className = 'col-md-3 d-grid';
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'btn btn-outline-danger';
      btn.textContent = 'Eliminar';
      btn.addEventListener('click', () => row.remove());
      colDel.appendChild(btn);
  
      row.appendChild(colProd);
      row.appendChild(colCant);
      row.appendChild(colDel);
      return row;
    }
  
    function labelWrap(text, el) {
      const wrap = document.createElement('div');
      const lab = document.createElement('label');
      lab.className = 'form-label';
      lab.textContent = text;
      wrap.appendChild(lab);
      wrap.appendChild(el);
      return wrap;
    }
  
    btnAdd.addEventListener('click', () => {
      container.appendChild(buildRow());
    });
  
    // Si venimos de editar, precargar detalles
    if (Array.isArray(window.__DETALLES__)) {
      window.__DETALLES__.forEach(d => container.appendChild(buildRow(d)));
    } else {
      // por defecto una fila
      container.appendChild(buildRow());
    }
  })();
  