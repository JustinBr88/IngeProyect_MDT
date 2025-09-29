/**
 * Funciones reutilizables para manejo de modales Bootstrap
 * Requiere que modelos.php esté incluido en la página
 */

console.log('Archivo modales.js cargado');

// Función para mostrar errores de validación
function mostrarErrores(errores) {
    console.log('mostrarErrores llamada con:', errores);
    const erroresLista = document.getElementById('erroresLista');
    erroresLista.innerHTML = '';
    
    const erroresArray = errores.split('\n');
    erroresArray.forEach(error => {
        if (error.trim()) {
            const li = document.createElement('li');
            li.innerHTML = `<i class="fa fa-times text-danger me-2"></i>${error.trim()}`;
            li.className = 'mb-2';
            erroresLista.appendChild(li);
        }
    });
    
    const modalErrores = new bootstrap.Modal(document.getElementById('modalErrores'));
    modalErrores.show();
}

// Función para confirmar guardado de cambios
function confirmarGuardar(callback) {
    console.log('confirmarGuardar llamada');
    const modalConfirmar = new bootstrap.Modal(document.getElementById('modalConfirmarGuardar'));
    const btnSiGuardar = document.getElementById('btnSiGuardar');
    
    // Limpiar eventos anteriores
    btnSiGuardar.removeEventListener('click', btnSiGuardar.clickHandler);
    
    // Agregar nuevo evento
    btnSiGuardar.clickHandler = function() {
        modalConfirmar.hide();
        callback();
    };
    btnSiGuardar.addEventListener('click', btnSiGuardar.clickHandler);
    
    modalConfirmar.show();
}

// Función para confirmar eliminación
function confirmarEliminar(callback) {
    console.log('confirmarEliminar llamada');
    const modalConfirmar = new bootstrap.Modal(document.getElementById('modalConfirmarEliminar'));
    const btnSiEliminar = document.getElementById('btnSiEliminar');
    
    // Limpiar eventos anteriores
    btnSiEliminar.removeEventListener('click', btnSiEliminar.clickHandler);
    
    // Agregar nuevo evento
    btnSiEliminar.clickHandler = function() {
        modalConfirmar.hide();
        callback();
    };
    btnSiEliminar.addEventListener('click', btnSiEliminar.clickHandler);
    
    modalConfirmar.show();
}

// Función para mostrar mensaje de éxito
function mostrarExito(titulo = '¡Operación exitosa!', mensaje = 'La operación se completó correctamente') {
    document.getElementById('exitoTitulo').textContent = titulo;
    document.getElementById('exitoMensaje').textContent = mensaje;
    
    const modalExito = new bootstrap.Modal(document.getElementById('modalExito'));
    modalExito.show();
}

// Función para mostrar información
function mostrarInfo(titulo = 'Información', mensaje = 'Mensaje informativo') {
    document.getElementById('infoTitulo').textContent = titulo;
    document.getElementById('infoMensaje').textContent = mensaje;
    
    const modalInfo = new bootstrap.Modal(document.getElementById('modalInfo'));
    modalInfo.show();
}

// Función para confirmar acción personalizada
function confirmarAccion(titulo, mensaje, callback, textoBoton = 'Sí, continuar') {
    // Crear modal temporal si no existe uno específico
    let modalConfirmarAccion = document.getElementById('modalConfirmarAccion');
    
    if (!modalConfirmarAccion) {
        modalConfirmarAccion = document.createElement('div');
        modalConfirmarAccion.className = 'modal fade';
        modalConfirmarAccion.id = 'modalConfirmarAccion';
        modalConfirmarAccion.setAttribute('tabindex', '-1');
        modalConfirmarAccion.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-center">
                    <div class="modal-body">
                        <div style="font-size:3rem;color:#ffc107;">
                            <i class="fa fa-question-circle"></i>
                        </div>
                        <h4 class="mt-2 mb-3" id="accionTitulo">${titulo}</h4>
                        <p id="accionMensaje">${mensaje}</p>
                        <button type="button" class="btn btn-warning me-2" id="btnSiAccion">${textoBoton}</button>
                        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modalConfirmarAccion);
    } else {
        document.getElementById('accionTitulo').textContent = titulo;
        document.getElementById('accionMensaje').textContent = mensaje;
        document.getElementById('btnSiAccion').textContent = textoBoton;
    }
    
    const modal = new bootstrap.Modal(modalConfirmarAccion);
    const btnSiAccion = document.getElementById('btnSiAccion');
    
    // Limpiar eventos anteriores
    btnSiAccion.removeEventListener('click', btnSiAccion.clickHandler);
    
    // Agregar nuevo evento
    btnSiAccion.clickHandler = function() {
        modal.hide();
        callback();
    };
    btnSiAccion.addEventListener('click', btnSiAccion.clickHandler);
    
    modal.show();
}

// Función helper para manejo de errores AJAX
function manejarRespuestaAjax(response, onSuccess, onError = null) {
    if (response.success) {
        if (typeof onSuccess === 'function') {
            onSuccess(response);
        }
    } else {
        if (onError && typeof onError === 'function') {
            onError(response);
        } else {
            mostrarErrores(response.error || 'Error desconocido');
        }
    }
}

// Función helper para manejo de errores de fetch
function manejarErrorFetch(error) {
    mostrarErrores('Error de conexión: ' + error.message);
}
