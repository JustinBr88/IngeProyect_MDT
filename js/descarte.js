// JavaScript para gestión de descartes

// Confirmar y ejecutar marcado de descarte
function confirmarDescarte() {
    const equipoId = document.getElementById('equipoDescarte').value;
    const tecnico = document.getElementById('tecnicoDescarte').value.trim();
    const observaciones = document.getElementById('observacionesDescarte').value.trim();
    
    // Validaciones
    if (!equipoId) {
        alert('Por favor seleccione un equipo');
        return;
    }
    
    if (!tecnico) {
        alert('Por favor ingrese el nombre del técnico');
        return;
    }
    
    if (!observaciones || observaciones.length < 20) {
        alert('Por favor proporcione observaciones técnicas detalladas (mínimo 20 caracteres)');
        return;
    }
    
    // Confirmación adicional
    if (!confirm('¿Está seguro de marcar este equipo como descarte? Esta acción liberará el equipo si está asignado.')) {
        return;
    }
    
    // Enviar datos
    const formData = new FormData();
    formData.append('accion', 'marcar_descarte');
    formData.append('inventario_id', equipoId);
    formData.append('tecnico', tecnico);
    formData.append('observaciones', observaciones);
    
    fetch('../ajax/descarte.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Equipo marcado como descarte correctamente');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la solicitud');
    });
}

// Ver detalle completo de un equipo en descarte
function verDetalleDescarte(inventarioId) {
    const formData = new FormData();
    formData.append('accion', 'obtener_detalle_descarte');
    formData.append('inventario_id', inventarioId);
    
    // Mostrar modal con loading
    document.getElementById('contenidoDetalleDescarte').innerHTML = 
        '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';
    
    new bootstrap.Modal(document.getElementById('modalDetalleDescarte')).show();
    
    fetch('../ajax/descarte.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const detalle = data.detalle;
            const contenido = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Información del Equipo</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>ID:</strong></td>
                                <td>${detalle.id}</td>
                            </tr>
                            <tr>
                                <td><strong>Nombre:</strong></td>
                                <td>${detalle.nombre_equipo}</td>
                            </tr>
                            <tr>
                                <td><strong>Categoría:</strong></td>
                                <td><span class="badge bg-secondary">${detalle.categoria || 'Sin categoría'}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Marca:</strong></td>
                                <td>${detalle.marca}</td>
                            </tr>
                            <tr>
                                <td><strong>Modelo:</strong></td>
                                <td>${detalle.modelo}</td>
                            </tr>
                            <tr>
                                <td><strong>Serie:</strong></td>
                                <td>${detalle.numero_serie}</td>
                            </tr>
                            <tr>
                                <td><strong>Fecha Ingreso:</strong></td>
                                <td>${new Date(detalle.fecha_ingreso).toLocaleDateString('es-ES')}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-danger">Información del Descarte</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Fecha Descarte:</strong></td>
                                <td>${new Date(detalle.fecha_descarte).toLocaleString('es-ES')}</td>
                            </tr>
                            <tr>
                                <td><strong>Técnico:</strong></td>
                                <td>${detalle.tecnico_descarte}</td>
                            </tr>
                        </table>
                        
                        <h6 class="text-warning mt-3">Observaciones Técnicas</h6>
                        <div class="border p-3 bg-light rounded">
                            ${detalle.observaciones_descarte.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 text-center">
                    <button class="btn btn-success" onclick="restaurarEquipo(${detalle.id})">
                        <i class="fas fa-undo"></i> Restaurar Equipo
                    </button>
                </div>
            `;
            
            document.getElementById('contenidoDetalleDescarte').innerHTML = contenido;
        } else {
            document.getElementById('contenidoDetalleDescarte').innerHTML = 
                '<div class="alert alert-danger">Error: ' + data.message + '</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('contenidoDetalleDescarte').innerHTML = 
            '<div class="alert alert-danger">Error al cargar el detalle</div>';
    });
}

// Restaurar equipo del descarte
function restaurarEquipo(inventarioId) {
    if (!confirm('¿Está seguro de restaurar este equipo? Volverá a estar disponible para asignaciones.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('accion', 'restaurar_descarte');
    formData.append('inventario_id', inventarioId);
    
    fetch('../ajax/descarte.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Equipo restaurado correctamente');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la solicitud');
    });
}

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sistema de descartes cargado');
});
