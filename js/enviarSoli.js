document.addEventListener('DOMContentLoaded', function() {
    // Al hacer clic en "Solicitar"
    document.querySelectorAll('.btn-solicitar').forEach(btn => {
        btn.addEventListener('click', function() {
            const tr = btn.closest('tr');
            const nombreEquipo = tr.getAttribute('data-nombre');
            const inventarioId = tr.getAttribute('data-id');
            
            // Llenar los campos del modal
            document.getElementById('nombreEquipoSolicitado').textContent = nombreEquipo;
            document.getElementById('inputInventarioId').value = inventarioId;
            document.getElementById('inputNombreEquipo').value = nombreEquipo;
            
            // Limpiar el textarea del motivo
            document.getElementById('motivoSolicitud').value = '';
            
            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('modalSolicitar'));
            modal.show();
        });
    });

    // Al confirmar solicitud
    document.getElementById('formSolicitar').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const inventarioId = document.getElementById('inputInventarioId').value;
        const nombreEquipo = document.getElementById('inputNombreEquipo').value;
        const motivo = document.getElementById('motivoSolicitud').value.trim();
        
        // Validar que el motivo no esté vacío
        if (!motivo) {
            alert('Por favor, ingresa el motivo de tu solicitud.');
            return;
        }
        
        // Enviar la solicitud
        fetch('conexinvetcolab.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                inventario_id: inventarioId,
                nombre_equipo: nombreEquipo,
                motivo: motivo
            })
        })
        .then(r => r.json())
        .then(resp => {
            if (resp.success) {
                alert('¡Solicitud enviada correctamente!');
                // Cerrar el modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalSolicitar'));
                modal.hide();
                // Recargar la página para actualizar el estado
                location.reload();
            } else {
                alert('Error: ' + resp.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión. Inténtalo de nuevo.');
        });
    });
});