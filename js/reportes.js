// JavaScript para reportes
document.addEventListener('DOMContentLoaded', function() {
    console.log('Reportes cargados correctamente');
});

// Ver detalle de equipos por categoría
function verDetalle(categoriaId, categoriaNombre) {
    document.getElementById('categoriaDetalle').textContent = categoriaNombre;
    
    // Mostrar modal con contenido de carga
    document.getElementById('contenidoDetalle').innerHTML = '<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando detalles...</div>';
    new bootstrap.Modal(document.getElementById('modalDetalle')).show();
    
    // Cargar datos por AJAX
    const formData = new FormData();
    formData.append('categoria_id', categoriaId);
    
    fetch('../ajax/detalle_categoria.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(data => {
        document.getElementById('contenidoDetalle').innerHTML = data;
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('contenidoDetalle').innerHTML = 
            '<div class="alert alert-danger">Error al cargar el detalle: ' + error.message + '</div>';
    });
}

// Exportar todos los datos de inventario a Excel
function exportarExcel(tipo) {
    let url = '../ajax/exportar_excel.php?tipo=' + tipo + '&formato=excel';
    window.open(url, '_blank');
}

// Exportar datos de una categoría específica
function exportarCategoria(categoriaId, categoriaNombre) {
    let url = '../ajax/exportar_excel.php?tipo=categoria&categoria_id=' + categoriaId + '&categoria=' + encodeURIComponent(categoriaNombre) + '&formato=excel';
    window.open(url, '_blank');
}

// Aplicar filtros
function aplicarFiltros() {
    const categoria = document.getElementById('filtroCategoria').value;
    const estado = document.getElementById('filtroEstado').value;
    const fechaDesde = document.getElementById('fechaDesde').value;
    const fechaHasta = document.getElementById('fechaHasta').value;
    
    // Mostrar indicador de carga
    const tbody = document.querySelector('#tablaReportes tbody');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>';
    
    // Cargar datos filtrados
    const params = new URLSearchParams();
    if (categoria) params.append('categoria', categoria);
    if (estado) params.append('estado', estado);
    if (fechaDesde) params.append('fecha_desde', fechaDesde);
    if (fechaHasta) params.append('fecha_hasta', fechaHasta);
    
    fetch('../ajax/reporte_filtrado.php?' + params.toString())
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(data => {
        // Actualizar tabla con datos filtrados
        tbody.innerHTML = data;
    })
    .catch(error => {
        console.error('Error:', error);
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error al aplicar filtros: ' + error.message + '</td></tr>';
    });
}

// Limpiar filtros
function limpiarFiltros() {
    document.getElementById('filtroCategoria').value = '';
    document.getElementById('filtroEstado').value = '';
    document.getElementById('fechaDesde').value = '';
    document.getElementById('fechaHasta').value = '';
    
    // Recargar página para mostrar todos los datos
    location.reload();
}

// Exportar datos filtrados
function exportarFiltrado() {
    const categoria = document.getElementById('filtroCategoria').value;
    const estado = document.getElementById('filtroEstado').value;
    const fechaDesde = document.getElementById('fechaDesde').value;
    const fechaHasta = document.getElementById('fechaHasta').value;
    
    const params = new URLSearchParams();
    params.append('tipo', 'filtrado');
    params.append('formato', 'excel');
    if (categoria) params.append('categoria', categoria);
    if (estado) params.append('estado', estado);
    if (fechaDesde) params.append('fecha_desde', fechaDesde);
    if (fechaHasta) params.append('fecha_hasta', fechaHasta);
    
    const url = '../ajax/exportar_excel.php?' + params.toString();
    window.open(url, '_blank');
}

// Función para actualizar gráficos (opcional)
function actualizarGraficos() {
    // Aquí se pueden agregar gráficos con Chart.js si se desea
    console.log('Gráficos actualizados');
}
