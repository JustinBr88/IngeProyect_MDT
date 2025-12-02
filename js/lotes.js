// js/lotes.js - control UI LOTES y Detalles (exponer helpers)
document.addEventListener('DOMContentLoaded', function() {
    const apiLotes = 'conexlotes.php';
    const apiInv = 'conexinventario.php';
    const tbodyLotes = document.getElementById('tbodyLotes');
    const tbodyEquiposLote = document.getElementById('tbodyEquiposLote');
    const formLote = document.getElementById('formLote');
    const btnAdd = document.getElementById('btnAdd');
    const btnAddEquipoGlobal = document.getElementById('btnAddEquipo');
    const btnAddEquipoModal = document.getElementById('btnAddEquipoModal');
    let currentLoteId = null;

    const modalLoteEl = document.getElementById('modalLote');
    const modalDetallesEl = document.getElementById('modalDetalles');
    const modalNuevoEl = document.getElementById('modalNuevo');

    // init bootstrap modal instances if available
    let modalLote = null, modalDetalles = null, modalNuevo = null;
    if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal === 'function') {
        try {
            if (modalLoteEl) modalLote = new bootstrap.Modal(modalLoteEl, {});
            if (modalDetallesEl) modalDetalles = new bootstrap.Modal(modalDetallesEl, {});
            if (modalNuevoEl) modalNuevo = new bootstrap.Modal(modalNuevoEl, {});
        } catch (e) { console.warn('Bootstrap modal init error', e); }
    }

    function escapeHtml(t){ if(!t) return ''; return t.replace(/[&<>\"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":'&#39;'}[m])); }

        // helper: safe closest (works even if event target is a text node)
        function closestButton(el, selector) {
            while (el) {
                if (el.nodeType === 1 && el.matches && el.matches(selector)) return el;
                el = el.parentElement;
            }
            return null;
        }

    async function fetchLotes() {
        try {
            const r = await fetch(`${apiLotes}?action=list`);
            if (!r.ok) { console.error('fetchLotes status', r.status); return; }
                const text = await r.text();
                if (!text || text.trim() === '') { console.error('fetchLotes empty response'); return; }
                let js;
                try { js = JSON.parse(text); } catch (e) { console.error('fetchLotes non-json', text); return; }
                if (!js || !js.success) { console.error('fetchLotes', js); return; }
                renderLotes(js.data || []);
        } catch (e) { console.error('fetchLotes err', e); }
    }

    function renderLotes(lotes) {
        if (!tbodyLotes) return;
        tbodyLotes.innerHTML = '';
        lotes.forEach(l => {
            const imgUrl = l.imagen ? `../uploads/${l.imagen}` : '../img/noimage.png';
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><img src="${imgUrl}" style="width:60px;height:40px;object-fit:cover" alt=""></td>
                <td>${l.id}</td>
                <td>${escapeHtml(l.nombre_equipo)}</td>
                <td>${escapeHtml(l.categoria_nombre ?? '')}</td>
                <td>${escapeHtml(l.marca ?? '')}</td>
                <td>${l.costo ?? ''}</td>
                <td>${l.tiempo_depreciacion ?? ''}</td>
                <td>${l.cantidad ?? 0}</td>
                <td>
                  <button type="button" class="btn btn-sm btn-warning btn-detalles" data-id="${l.id}" data-nombre="${escapeHtml(l.nombre_equipo)}">Detalles</button>
                  <button type="button" class="btn btn-sm btn-primary btn-edit" data-id="${l.id}">Cambiar lote</button>
                  <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="${l.id}">Eliminar</button>
                </td>
            `;
            tbodyLotes.appendChild(tr);
        });
    }

    // open lote modal
    if (btnAdd) {
        btnAdd.addEventListener('click', (e) => {
            e.preventDefault();
            if (formLote) formLote.reset();
            const idIn = document.getElementById('lote_id'); if (idIn) idIn.value = '';
            const title = document.getElementById('modalLoteTitle'); if (title) title.innerText = 'Nuevo Lote';
            try { if (modalLote) modalLote.show(); else { modalLoteEl.classList.add('show'); modalLoteEl.style.display='block'; document.body.classList.add('modal-open'); } } catch(e){}
        });
    }

    // añadir equipo global
    if (btnAddEquipoGlobal) {
        btnAddEquipoGlobal.addEventListener('click', (e)=> {
            e.preventDefault();
            const fn = document.getElementById('formNuevo'); if (fn) fn.reset();
            try { if (modalNuevo) modalNuevo.show(); else { modalNuevoEl.classList.add('show'); modalNuevoEl.style.display='block'; document.body.classList.add('modal-open'); } } catch(e){}
        });
    }

    // añadir equipo desde modalDetalles -> mostrar inline quick form
    if (btnAddEquipoModal) {
        btnAddEquipoModal.addEventListener('click', (e)=>{
            e.preventDefault();
            console.debug('btnAddEquipoModal clicked, currentLoteId=', currentLoteId);
            if (!currentLoteId) return alert('No hay lote seleccionado.');
            const inlineDiv = document.getElementById('inlineAddEquipo');
            const inlineForm = document.getElementById('inlineAddEquipoForm');
            if (!inlineDiv || !inlineForm) return alert('Formulario rápido no disponible.');
            inlineForm.reset();
            const loteSelect = document.getElementById('inline_lote_id');
            if (loteSelect) loteSelect.value = currentLoteId;
            inlineDiv.style.display = 'block';
            const s = document.getElementById('inline_numero_serie'); if (s) s.focus();
        });
    }

    // handle opening details and loading equipos
    document.addEventListener('click', async function(e) {
        const btn = closestButton(e.target, 'button.btn-detalles');
        if (!btn) return;
        e.preventDefault();
        const id = btn.dataset.id;
        const nombre = btn.dataset.nombre || '';
        console.debug('btn-detalles clicked, lote id=', id);
        currentLoteId = id;
        window.currentLoteId = id;
        const detallesNombreEl = document.getElementById('detallesLoteNombre');
        if (detallesNombreEl) detallesNombreEl.innerText = ` - ${nombre}`;
        await loadEquiposByLote(id);
        try { if (modalDetalles) modalDetalles.show(); else { modalDetallesEl.classList.add('show'); modalDetallesEl.style.display='block'; document.body.classList.add('modal-open'); } } catch(e){}
    });

    async function loadEquiposByLote(lote_id) {
        if (!tbodyEquiposLote) return;
        tbodyEquiposLote.innerHTML = '<tr><td colspan="8" class="text-center">Cargando...</td></tr>';
        try {
            const r = await fetch(`${apiInv}?action=list_by_lote&lote_id=${encodeURIComponent(lote_id)}`);
            const text = await r.text();
            if (!text || text.trim()==='') { tbodyEquiposLote.innerHTML = `<tr><td colspan="8">No hay equipos</td></tr>`; return; }
            let js;
            try { js = JSON.parse(text); } catch (e) { console.error('list_by_lote not JSON', text); tbodyEquiposLote.innerHTML = `<tr><td colspan="8">Error del servidor</td></tr>`; return; }
            if (!js.success) { tbodyEquiposLote.innerHTML = `<tr><td colspan="8">${js.message || 'Error'}</td></tr>`; return; }
            const rows = js.data || [];
            if (!rows.length) { tbodyEquiposLote.innerHTML = `<tr><td colspan="8">No hay equipos en este lote.</td></tr>`; return; }
            tbodyEquiposLote.innerHTML = '';
            rows.forEach((r,i)=> {
                const img = r.imagen ? `../uploads/${r.imagen}` : '../img/noimage.png';
                const tr = document.createElement('tr');
                tr.innerHTML = `
                  <td>${r.equipo_num ?? (i+1)}</td>
                  <td data-id="${r.id}">${r.id}</td>
                  <td>${escapeHtml(r.numero_serie ?? '')}</td>
                  <td>${escapeHtml(r.modelo ?? '')}</td>
                  <td>${escapeHtml(r.marca ?? '')}</td>
                  <td>${escapeHtml(r.estado ?? '')}</td>
                  <td><img src="${img}" style="width:60px;height:40px;object-fit:cover"></td>
                  <td>
                    <button type="button" class="btn btn-sm btn-primary btn-edit-eq" data-id="${r.id}">Cambiar lote</button>
                    <button type="button" class="btn btn-sm btn-danger btn-del-eq" data-id="${r.id}">Eliminar</button>
                  </td>`;
                tbodyEquiposLote.appendChild(tr);
            });
        } catch (err) {
            console.error(err);
            tbodyEquiposLote.innerHTML = `<tr><td colspan="8">Error al cargar equipos</td></tr>`;
        }
    }

    // expose helpers
    window.loadEquiposByLote = loadEquiposByLote;
    window.currentLoteId = currentLoteId;
    window.fetchLotes = fetchLotes;

    // init
    fetchLotes();

    // Inline add equipo submit handler (quick form inside modalDetalles)
    document.addEventListener('submit', async function(ev){
        const form = ev.target && ev.target.id === 'inlineAddEquipoForm' ? ev.target : null;
        if (!form) return;
        ev.preventDefault();
        const loteId = form.querySelector('#inline_lote_id') && form.querySelector('#inline_lote_id').value;
        const numeroSerie = form.querySelector('#inline_numero_serie') && form.querySelector('#inline_numero_serie').value.trim();
        const modelo = form.querySelector('#inline_modelo') && form.querySelector('#inline_modelo').value.trim() || null;
        if (!loteId) { alert('Seleccione un lote'); return; }
        if (!numeroSerie) { alert('Número de serie obligatorio'); return; }
        if (!/^\d+$/.test(numeroSerie)) { alert('El número de serie solo debe contener dígitos (0-9).'); const sEl = document.getElementById('inline_numero_serie'); if (sEl && typeof sEl.focus === 'function') sEl.focus(); return; }

        try {
            // pre-check numero_serie uniqueness
            try {
                const chk = await fetch(`${apiInv}?action=exists&numero_serie=${encodeURIComponent(numeroSerie)}`);
                const txtChk = await chk.text();
                let jchk; try { jchk = JSON.parse(txtChk); } catch(e){ jchk = null; }
                if (jchk && jchk.success && jchk.data && jchk.data.exists) {
                    alert('Ese número de serie ya existe en inventario. Introduzca otro.');
                    const sEl = document.getElementById('inline_numero_serie'); if (sEl && typeof sEl.focus === 'function') sEl.focus();
                    return;
                }
            } catch(e) { console.warn('exists check failed', e); }

            const rL = await fetch(`${apiLotes}?action=get&id=${encodeURIComponent(loteId)}`);
            const txt = await rL.text();
            let jl; try { jl = JSON.parse(txt); } catch(e){ console.error('lote get not json', txt); alert('Error obteniendo datos del lote'); return; }
            if (!jl.success) { alert(jl.message || 'Lote no encontrado'); return; }
            const lote = jl.data || {};

            const fd = new FormData();
            fd.append('lote_id', loteId);
            fd.append('require_lote', '1');
            fd.append('numero_serie', numeroSerie);
            if (modelo) fd.append('modelo', modelo);
            if (lote.nombre_equipo !== undefined) fd.append('nombre_equipo', lote.nombre_equipo);
            if (lote.categoria_id !== undefined) fd.append('categoria_id', lote.categoria_id);
            if (lote.marca !== undefined) fd.append('marca', lote.marca);
            if (lote.costo !== undefined) fd.append('costo', lote.costo);
            if (lote.tiempo_depreciacion !== undefined) fd.append('tiempo_depreciacion', lote.tiempo_depreciacion);
            if (lote.descripcion !== undefined) fd.append('descripcion', lote.descripcion);
            if (lote.imagen !== undefined && lote.imagen !== null && lote.imagen !== '') fd.append('imagen', lote.imagen);

            const r = await fetch(`${apiInv}?action=alta`, { method: 'POST', body: fd });
            const text = await r.text();
            console.debug('inline alta response status=', r.status, 'text=', text);
            let js; try { js = JSON.parse(text); } catch(e){ console.error('alta not json', text); alert('Respuesta inesperada del servidor al crear equipo. Revisa la consola.'); return; }
            if (!js.success) { alert(js.message || text || 'Error al crear equipo'); return; }
            alert(js.message || 'Equipo creado');
            const inlineDiv2 = document.getElementById('inlineAddEquipo'); if (inlineDiv2) inlineDiv2.style.display = 'none';
            if (typeof window.loadEquiposByLote === 'function') window.loadEquiposByLote(currentLoteId);
        } catch (err) {
            console.error('Error creating equipo inline', err);
            alert('Error al crear el equipo (mira consola)');
        }
    });

    // sanitize numero_serie inputs as the user types (strip non-digits)
    function attachDigitFilter(id) {
        try {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', function(e){
                const pos = this.selectionStart;
                const cleaned = this.value.replace(/\D+/g,'');
                if (cleaned !== this.value) {
                    this.value = cleaned;
                    try { this.setSelectionRange(pos-1, pos-1); } catch(e) {}
                }
            });
        } catch(e) { console.warn('attachDigitFilter failed for', id, e); }
    }
    attachDigitFilter('inline_numero_serie');
    attachDigitFilter('nuevo_numero_serie');
});

// ------- Enhancements: form submit, edit/delete handlers and robust modal close -------
(function(){
    // small helper to close a modal safely even if bootstrap is blocked
    function closeModalSafe(modalInstance, modalEl) {
        try {
            if (modalInstance && typeof modalInstance.hide === 'function') {
                modalInstance.hide();
                return;
            }
        } catch (e) {
            console.warn('closeModalSafe bootstrap hide failed', e);
        }
        // fallback: aggressively remove classes, backdrops and restore page state
        try {
            // hide the provided modal element if present
            if (modalEl) {
                modalEl.classList.remove('show');
                modalEl.style.display = 'none';
                modalEl.setAttribute('aria-hidden', 'true');
                modalEl.removeAttribute('aria-modal');
            }
            // hide any other modals that may be stuck
            document.querySelectorAll('.modal.show').forEach(m => {
                try {
                    m.classList.remove('show');
                    m.style.display = 'none';
                    m.setAttribute('aria-hidden', 'true');
                    m.removeAttribute('aria-modal');
                } catch(e) { /* ignore */ }
            });
            // remove modal-open class and restore body overflow/padding
            try { document.body.classList.remove('modal-open'); } catch(e){}
            try { document.body.style.overflow = ''; } catch(e){}
            try { document.body.style.paddingRight = ''; } catch(e){}
            // remove any modal backdrops
            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
            // re-enable pointer events and enable controls inside modals
            document.querySelectorAll('.modal').forEach(m => {
                try {
                    m.style.pointerEvents = 'auto';
                    m.querySelectorAll('button, input, select, textarea').forEach(i => i.removeAttribute('disabled'));
                } catch(e) {}
            });
            // ensure focus released
            try { if (document.activeElement && document.activeElement.blur) document.activeElement.blur(); } catch(e){}
        } catch (e) { console.warn('closeModalSafe fallback failed', e); }
    }

    // extra helper: unstick any possible modal state (exposed for debugging)
    function unstickAllModals() {
        try { closeModalSafe(null, null); } catch(e) { console.warn('unstickAllModals closeModalSafe failed', e); }
        try {
            // also try to remove any full-screen overlays or custom dimmers
            document.querySelectorAll('[role="dialog"].show, .modal-backdrop, .overlay, .dimmer').forEach(el => el.remove());
        } catch(e) { /* ignore */ }
    }

    // close modals when user presses Escape as a last-resort
    document.addEventListener('keydown', function(ev){
        if (ev.key === 'Escape' || ev.key === 'Esc') {
            unstickAllModals();
        }
    });

    // local helper for delegated handlers in this closure
    function closestButtonLocal(el, selector) {
        while (el) {
            if (el.nodeType === 1 && el.matches && el.matches(selector)) return el;
            el = el.parentElement;
        }
        return null;
    }

    // locate the things we need (defensive lookups in case file loaded twice)
    const formLote = document.getElementById('formLote');
    const modalLoteEl = document.getElementById('modalLote');
    let modalLoteInstance = (window.bootstrap && window.bootstrap.Modal && modalLoteEl) ? window.bootstrap.Modal.getInstance(modalLoteEl) : null;

    if (formLote) {
        formLote.addEventListener('submit', async function(ev){
            ev.preventDefault();
            const api = 'conexlotes.php?action=save';
            const fd = new FormData(formLote);
            try {
                const r = await fetch(api, { method: 'POST', body: fd });
                const text = await r.text();
                console.debug('conexlotes.save response status=', r.status, 'text=', text);
                let js;
                try {
                    js = JSON.parse(text);
                } catch (e) {
                    console.error('conexlotes.save non-json', text);
                    alert('Respuesta inesperada del servidor al guardar lote. Revisa la consola (Network -> conexlotes.php) para ver la respuesta completa.');
                    return;
                }
                if (!js.success) {
                    alert(js.message || text || 'Error al guardar lote');
                    return;
                }
                // success: refresh list and close modal
                try { if (!modalLoteInstance && window.bootstrap && window.bootstrap.Modal && modalLoteEl) modalLoteInstance = new window.bootstrap.Modal(modalLoteEl); } catch(e){}
                // Try multiple ways to close the modal to cover both create and edit flows
                try {
                    if (modalLoteInstance && typeof modalLoteInstance.hide === 'function') {
                        modalLoteInstance.hide();
                    }
                } catch(e) {}
                try {
                    if (typeof modalLote !== 'undefined' && modalLote && typeof modalLote.hide === 'function') {
                        modalLote.hide();
                    }
                } catch(e) {}
                // Fallback aggressive cleanup
                closeModalSafe(modalLoteInstance, modalLoteEl);
                try { if (window._lotes_unstickAll) window._lotes_unstickAll(); } catch(e){}
                // ensure form reset and focus release
                try { formLote && formLote.reset(); } catch(e) {}
                // reset currentLoteId when creating new
                const idVal = fd.get('id');
                if (!idVal || idVal === '' || idVal === '0') window.currentLoteId = null;
                // refresh list
                try { if (typeof window.fetchLotes === 'function') window.fetchLotes(); } catch(e) { /* ignore */ }
                document.dispatchEvent(new CustomEvent('lotes:changed', { detail: js.data || {} }));
            } catch (err) {
                console.error('Error saving lote', err);
                alert('Error guardando el lote, revisa la consola.');
            }
        });
    }

    // delegate edit / delete buttons in the lotes table
    document.addEventListener('click', async function(e){
        const editBtn = closestButtonLocal(e.target, 'button.btn-edit');
        if (editBtn) {
            e.preventDefault();
            const id = editBtn.dataset.id;
            if (!id) return alert('ID inválido');
            try {
                const r = await fetch(`conexlotes.php?action=get&id=${encodeURIComponent(id)}`);
                const text = await r.text();
                let js;
                try { js = JSON.parse(text); } catch(e){ console.error('get lote not json', text); alert('Error servidor'); return; }
                if (!js.success) { alert(js.message||'No encontrado'); return; }
                const data = js.data || {};
                // fill form
                const f = document.getElementById('formLote'); if (!f) return alert('Formulario no encontrado');
                f.querySelector('[name="id"]').value = data.id || '';
                f.querySelector('[name="nombre_equipo"]').value = data.nombre_equipo || '';
                f.querySelector('[name="categoria_id"]').value = data.categoria_id || '';
                f.querySelector('[name="marca"]').value = data.marca || '';
                f.querySelector('[name="costo"]').value = data.costo || '';
                f.querySelector('[name="tiempo_depreciacion"]').value = data.tiempo_depreciacion || '';
                f.querySelector('[name="descripcion"]').value = data.descripcion || '';
                // no need to set imagen file input
                const title = document.getElementById('modalLoteTitle'); if (title) title.innerText = 'Editar Lote';
                try { if (!modalLoteInstance && window.bootstrap && window.bootstrap.Modal && modalLoteEl) modalLoteInstance = new window.bootstrap.Modal(modalLoteEl); } catch(e){}
                // show modal
                try { modalLoteInstance && modalLoteInstance.show(); } catch(e){ if (modalLoteEl) { modalLoteEl.classList.add('show'); modalLoteEl.style.display='block'; document.body.classList.add('modal-open'); } }
            } catch (err) { console.error(err); alert('Error cargando lote'); }
            return;
        }

        const delBtn = closestButtonLocal(e.target, 'button.btn-delete');
        if (delBtn) {
            e.preventDefault();
            const id = delBtn.dataset.id;
            if (!id) return alert('ID inválido');
            if (!confirm('¿Seguro que quieres eliminar este lote? Esta acción no puede deshacerse.')) return;
            try {
                const fd = new FormData(); fd.append('id', id);
                const r = await fetch('conexlotes.php?action=delete', { method: 'POST', body: fd });
                const text = await r.text();
                let js; try { js = JSON.parse(text); } catch(e){ console.error('delete not json', text); alert('Respuesta inesperada'); return; }
                if (!js.success) return alert(js.message || 'No se pudo eliminar');
                // refresh lotes
                try { if (typeof window.fetchLotes === 'function') window.fetchLotes(); } catch(e) {}
                document.dispatchEvent(new CustomEvent('lotes:changed', { detail: { id: id } }));
            } catch (err) { console.error(err); alert('Error al eliminar lote'); }
            return;
        }
    });

    // ensure clicks on [data-bs-dismiss] also close modals when bootstrap blocked
    document.addEventListener('click', function(e){
        const el = closestButtonLocal(e.target, '[data-bs-dismiss="modal"]');
        if (!el) return;
        // find closest modal parent
        const modalEl = el.closest && el.closest('.modal');
        if (!modalEl) return;
        let inst = null;
        try { inst = (window.bootstrap && window.bootstrap.Modal) ? window.bootstrap.Modal.getInstance(modalEl) : null; } catch(e) { inst = null; }
        closeModalSafe(inst, modalEl);
    });

    // expose helpers for debugging
    window._lotes_closeModalSafe = closeModalSafe;
    window._lotes_unstickAll = unstickAllModals;
})();