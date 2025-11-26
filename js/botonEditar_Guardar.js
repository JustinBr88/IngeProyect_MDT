// botonEditar_Guardar.js - versión final defensiva (asegúrate de reemplazar el archivo completo)

document.addEventListener('DOMContentLoaded', function () {
    console.log('botonEditar_Guardar cargado (final)');

    function normalizeButtonTarget(target) {
        if (!target) return null;
        if (target.tagName === 'BUTTON') return target;
        return target.closest ? target.closest('button') : null;
    }

    // Safe getter for elements (id or selector or name)
    function safeElement(container, queries) {
        for (const q of queries) {
            if (!q) continue;
            let el = null;
            if (q.startsWith('#')) el = document.getElementById(q.slice(1));
            else if (q.startsWith('name=')) el = (container && container.elements && container.elements[q.slice(5)]) || (container && container.querySelector(`[name="${q.slice(5)}"]`)) || document.querySelector(`[name="${q.slice(5)}"]`);
            else el = (container && container.querySelector(q)) || document.querySelector(q);
            if (el) return el;
        }
        return null;
    }

    document.addEventListener('click', function (e) {
        const btn = normalizeButtonTarget(e.target);
        if (!btn) return;

        // mover equipo
        if (btn.classList.contains('btn-edit-eq')) {
            e.preventDefault();
            const id = btn.dataset.id;
            if (!id) return alert('ID inválido');
            const newLote = prompt('ID de lote destino (dejar vacío para cancelar)');
            if (!newLote) return;
            fetch('conexinventario.php?action=update', { method: 'POST',
                headers:{ 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, lote_id: newLote })
            })
            .then(async r => { const txt = await r.text(); try { return JSON.parse(txt); } catch(e){ return { success:false, message:txt }; } })
            .then(js => { if (!js.success) alert(js.message||js.error||'Error'); else { alert(js.message||'OK'); if (window.currentLoteId && String(window.currentLoteId)===String(newLote) && typeof window.loadEquiposByLote === 'function') window.loadEquiposByLote(window.currentLoteId); else location.reload(); } })
            .catch(err => { console.error(err); alert('Error de conexión'); });
            return;
        }

        // eliminar equipo
        if (btn.classList.contains('btn-del-eq')) {
            e.preventDefault();
            const id = btn.dataset.id;
            if (!id) return alert('ID inválido');
            if (!confirm('¿Eliminar equipo?')) return;
            const fd = new FormData(); fd.append('id', id);
            fetch('conexinventario.php?action=delete', { method:'POST', body: fd })
                .then(async r => { const txt = await r.text(); try { return JSON.parse(txt); } catch(e){ return { success:false, message:txt }; } })
                .then(js => { if (!js.success) alert(js.message||'Error'); else { alert(js.message||'Eliminado'); if (typeof window.loadEquiposByLote === 'function' && window.currentLoteId) window.loadEquiposByLote(window.currentLoteId); else location.reload(); } })
                .catch(err => { console.error(err); alert('Error de conexión'); });
            return;
        }
    });

    // formNuevo submit
    const formNuevo = document.getElementById('formNuevo');
    if (!formNuevo) { console.warn('formNuevo no encontrado'); return; }
    formNuevo.addEventListener('submit', async function(e){
        e.preventDefault();
        try {
            // check document functions exist
            if (typeof document.getElementById !== 'function') { console.error('document.getElementById no es una función; posible sobrescritura de document'); alert('Error interno: objeto document corrupto (ver consola)'); return; }

            // attempt primary safe lookups
            let numeroInput = safeElement(this, ['#nuevo_numero_serie', 'name=numero_serie', 'input[name="numero_serie"]']);
            let loteSelect = safeElement(this, ['#nuevo_lote_id', 'name=lote_id', 'select[name="lote_id"]']);

            // additional fallbacks in case DOM structure differs
            if (!numeroInput) {
                numeroInput = this.querySelector && (this.querySelector('input[name="numero_serie"]') || document.getElementById('nuevo_numero_serie')) || null;
            }
            if (!loteSelect) {
                loteSelect = this.querySelector && (this.querySelector('select[name="lote_id"]') || document.getElementById('nuevo_lote_id')) || null;
            }

            // read values safely (guard against null / missing .value)
            let numero = '';
            let lote = '';
            try { numero = numeroInput && ('value' in numeroInput) ? (numeroInput.value || '').trim() : ''; } catch (inner) { console.warn('leer numeroInput falló', inner); numero = ''; }
            try { lote = loteSelect && ('value' in loteSelect) ? (loteSelect.value || '').trim() : ''; } catch (inner) { console.warn('leer loteSelect falló', inner); lote = ''; }

            if (!numero) { alert('Número de serie obligatorio'); if (numeroInput && typeof numeroInput.focus==='function') numeroInput.focus(); return; }
            // enforce digits only
            if (!/^\d+$/.test(numero)) { alert('El número de serie solo debe contener dígitos (0-9).'); if (numeroInput && typeof numeroInput.focus==='function') numeroInput.focus(); return; }
            if (!lote) { alert('Selecciona un lote'); if (loteSelect && typeof loteSelect.focus==='function') loteSelect.focus(); return; }

            // prepare submit button ref (needed by checks) BEFORE async checks
            const submitBtn = this.querySelector('button[type="submit"]');
            const oldText = submitBtn ? submitBtn.innerHTML : null;

            // check existence of numero_serie before sending
            try {
                const chk = await fetch('conexinventario.php?action=exists&numero_serie=' + encodeURIComponent(numero));
                const txtchk = await chk.text();
                let jchk; try { jchk = JSON.parse(txtchk); } catch(e) { jchk = null; }
                if (jchk && jchk.success && jchk.data && jchk.data.exists) {
                    alert('Ese número de serie ya existe en inventario. Introduzca otro.');
                    if (numeroInput && typeof numeroInput.focus === 'function') numeroInput.focus();
                    return;
                }
            } catch (e) { console.warn('check exists failed', e); /* allow proceed if check fails */ }

            const fd = new FormData(this);
            fd.set('numero_serie', numero);
            fd.set('lote_id', lote);
            fd.set('require_lote','1');

            if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = 'Guardando...'; }

        fetch('conexinventario.php?action=alta', { method:'POST', body: fd })
            .then(async r => { const txt = await r.text(); try { return JSON.parse(txt); } catch(e){ console.error('alta no JSON', txt); return { success:false, message:'Respuesta no válida' }; } })
            .then(js => {
                if (!js.success) { alert(js.message || 'Error creando equipo'); return; }
                // refresh data first, then close modal robustly and reset form
                alert(js.message || 'Equipo creado');
                // try to refresh inline lists without reloading
                try {
                    if (window.currentLoteId && String(window.currentLoteId) === String(lote) && typeof window.loadEquiposByLote === 'function') {
                        window.loadEquiposByLote(window.currentLoteId);
                    } else if (typeof window.fetchLotes === 'function') {
                        window.fetchLotes();
                    }
                } catch(e) {
                    console.warn('refresh after alta failed', e);
                }

                // Ensure the main Inventory page reloads — try to navigate to the Inventario link if present
                try {
                    // prefer a nav link to Inventario if available
                    let invHref = null;
                    try {
                        const anchors = Array.from(document.querySelectorAll('a'));
                        const invAnchor = anchors.find(a => a.textContent && a.textContent.trim().toLowerCase().includes('inventario'));
                        if (invAnchor && invAnchor.href) invHref = invAnchor.href;
                    } catch(e) { /* ignore */ }
                    setTimeout(function(){
                        try {
                            if (invHref) {
                                // navigate explicitly to inventory main page
                                window.location.href = invHref;
                            } else {
                                // fallback: reload current page fully
                                window.location.reload();
                            }
                        } catch(e) { try { window.location.reload(); } catch(_) {} }
                    }, 900);
                } catch(e) {}

                // attempt to close modal using exposed helper or bootstrap instance or fallback
                try {
                    const modalEl = document.getElementById('modalNuevo');
                    // first try global helper from lotes.js
                    if (window._lotes_closeModalSafe) {
                        try { window._lotes_closeModalSafe(null, modalEl); } catch(e) { console.warn('helper close fail', e); }
                    } else if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                        try { const inst = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl); inst.hide(); } catch(e) { /* ignore */ }
                    } else if (modalEl) {
                        modalEl.classList.remove('show'); modalEl.style.display='none'; modalEl.setAttribute('aria-hidden','true'); document.body.classList.remove('modal-open'); document.querySelectorAll('.modal-backdrop').forEach(b=>b.remove());
                    }
                    if (window._lotes_unstickAll) try { window._lotes_unstickAll(); } catch(e){}
                } catch(e) { console.warn('cerrar modal error', e); }

                // reset the form
                try { if (formNuevo && typeof formNuevo.reset === 'function') formNuevo.reset(); } catch(e) {}
            })
            .catch(err => { console.error('Error alta', err); alert('Error de conexión'); })
            .finally(()=> { if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = oldText; } });
        } catch (err) {
            console.error('formNuevo submit error', err);
            alert('Error interno al procesar el formulario (ver consola)');
        }
    });
});