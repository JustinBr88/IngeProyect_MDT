document.addEventListener('DOMContentLoaded', function() {
    // QR
    document.querySelectorAll('.btn-qr').forEach(btn => {
        btn.addEventListener('click', function() {
            const tr = btn.closest('tr');
            const equipo = {
                nombre: tr.getAttribute('data-nombre'),
                categoria: tr.getAttribute('data-categoria'),
                marca: tr.getAttribute('data-marca'),
                modelo: tr.getAttribute('data-modelo'),
                serie: tr.getAttribute('data-serie'),
                costo: tr.getAttribute('data-costo'),
                ingreso: tr.getAttribute('data-ingreso'),
                depreciacion: tr.getAttribute('data-depreciacion'),
            };
            let qrText = `${equipo.nombre} | ${equipo.marca} ${equipo.modelo} | ${equipo.serie}`;
            let textoCompleto = `Equipo: ${equipo.nombre}
Categoría: ${equipo.categoria}
Marca: ${equipo.marca}
Modelo: ${equipo.modelo}
Serie: ${equipo.serie}
Costo: ${equipo.costo}
Ingreso: ${equipo.ingreso}
Depreciación: ${equipo.depreciacion}`;
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(qrText)}`;
            document.getElementById('qrContainer').innerHTML = `<img src="${qrUrl}" alt="Código QR" class="img-fluid">`;
            document.getElementById('qrEquipoDatos').innerHTML = textoCompleto.replace(/\n/g, '<br>');
            const modal = new bootstrap.Modal(document.getElementById('modalQr'));
            modal.show();
        });
    });

    // Aprobar/Rechazar
    document.querySelectorAll('.btn-aprobar, .btn-rechazar').forEach(btn => {
        btn.addEventListener('click', function() {
            const tr = btn.closest('tr');
            const solicitudId = tr.getAttribute('data-id');
            const accion = btn.classList.contains('btn-aprobar') ? 'aprobar' : 'rechazar';
            document.getElementById('inputSolicitudId').value = solicitudId;
            document.getElementById('inputAccion').value = accion;
            document.getElementById('accionTexto').textContent = accion === 'aprobar'
                ? '¿Confirma que desea aprobar esta solicitud?'
                : '¿Confirma que desea rechazar esta solicitud?';
            // show/hide motivo textarea for rejection
            try {
                const wrapper = document.getElementById('adminMotivoWrapper');
                const motivoEl = document.getElementById('adminMotivo');
                if (accion === 'rechazar') {
                    if (wrapper) wrapper.style.display = '';
                    if (motivoEl) { motivoEl.value = ''; try { motivoEl.focus(); } catch(e){} }
                } else {
                    if (wrapper) wrapper.style.display = 'none';
                }
            } catch(e) { console.warn('motivo toggle failed', e); }

            const modalEl = document.getElementById('modalAccion');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            // ensure textarea is enabled and focus AFTER modal is shown
            try {
                if (motivoEl) {
                    motivoEl.removeAttribute('readonly');
                    motivoEl.disabled = false;
                    motivoEl.removeAttribute('aria-hidden');
                }
                setTimeout(function(){ if (accion === 'rechazar' && motivoEl) try { motivoEl.focus(); } catch(e){} }, 200);
            } catch(e) { console.warn('focus motivo failed', e); }
        });
    });

    // Confirmar acción
    document.getElementById('formAccion').addEventListener('submit', function(e) {
        e.preventDefault();
        const solicitudId = document.getElementById('inputSolicitudId').value;
        const accion = document.getElementById('inputAccion').value;
        // include optional admin motivo when present
        const motivoEl = document.getElementById('adminMotivo');
        const motivo = (motivoEl && motivoEl.value) ? motivoEl.value.trim() : '';

        // Client-side validation: motivo is OPTIONAL when rejecting; if provided, require >=10 words
        if (accion === 'rechazar') {
            if (motivo) {
                // Count words
                const words = motivo.split(/\s+/).filter(w => w && w.length > 0);
                if (words.length < 10) {
                    alert('Si proporciona un motivo, este debe contener al menos 10 palabras. Actualmente tiene ' + words.length + '.');
                    if (motivoEl && typeof motivoEl.focus === 'function') motivoEl.focus();
                    return;
                }
            }
        }

        const payload = { solicitud_id: solicitudId, accion, motivo };

        // debug info
        try { console.debug('adminSoli.submit payload', payload); } catch(e) {}

        fetch('conexsolicitudes.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        })
        .then(async r => {
            const txt = await r.text();
            let resp = null;
            try { resp = JSON.parse(txt); } catch(e) { resp = { success:false, error: 'Respuesta no JSON: ' + txt }; }
            if (resp.success) {
                alert('Acción realizada correctamente.');
                location.reload();
            } else {
                alert('Error: ' + (resp.error || JSON.stringify(resp)));
                try { console.warn('adminSoli error response', resp); } catch(e) {}
            }
        })
        .catch(err => { console.error('adminSoli fetch error', err); alert('Error de conexión'); });
    });
});