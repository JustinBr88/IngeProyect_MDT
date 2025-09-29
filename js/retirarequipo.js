document.addEventListener('DOMContentLoaded', function() {
    // Retirar equipo
    document.querySelectorAll('.btn-retirar').forEach(btn => {
        btn.addEventListener('click', function() {
            const tr = btn.closest('tr');
            document.getElementById('nombreEquipoRetiro').textContent = tr.getAttribute('data-nombre');
            document.getElementById('inputAsignacionId').value = tr.getAttribute('data-id');
            document.getElementById('inputInventarioId').value = tr.getAttribute('data-inventario');
            document.getElementById('motivoRetiro').value = '';
            document.getElementById('nuevoEstado').value = 'inventario';
            const modal = new bootstrap.Modal(document.getElementById('modalRetirar'));
            modal.show();
        });
    });

    // Procesar retiro
    document.getElementById('formRetirar').addEventListener('submit', function(e) {
        e.preventDefault();
        const asignacionId = document.getElementById('inputAsignacionId').value;
        const inventarioId = document.getElementById('inputInventarioId').value;
        const motivo = document.getElementById('motivoRetiro').value;
        const nuevoEstado = document.getElementById('nuevoEstado').value;
        fetch('api_retirar_equipo.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({asignacion_id: asignacionId, inventario_id: inventarioId, motivo, nuevo_estado: nuevoEstado})
        })
        .then(r => r.json())
        .then(resp => {
            if (resp.success) {
                alert('Equipo retirado exitosamente.');
                location.reload();
            } else {
                alert('Error: ' + resp.error);
            }
        });
    });
});