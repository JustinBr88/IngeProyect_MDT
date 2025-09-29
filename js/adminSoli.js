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
            const modal = new bootstrap.Modal(document.getElementById('modalAccion'));
            modal.show();
        });
    });

    // Confirmar acción
    document.getElementById('formAccion').addEventListener('submit', function(e) {
        e.preventDefault();
        const solicitudId = document.getElementById('inputSolicitudId').value;
        const accion = document.getElementById('inputAccion').value;
        fetch('conexsolicitudes.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({solicitud_id: solicitudId, accion})
        })
        .then(r => r.json())
        .then(resp => {
            if (resp.success) {
                alert('Acción realizada correctamente.');
                location.reload();
            } else {
                alert('Error: ' + resp.error);
            }
        });
    });
});