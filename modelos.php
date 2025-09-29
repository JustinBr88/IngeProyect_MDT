<!-- Modal de errores de validación -->
<div class="modal fade" id="modalErrores" tabindex="-1" aria-labelledby="modalErroresLabel">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-body">
        <div style="font-size:3rem;color:#dc3545;">
          <i class="fa fa-exclamation-triangle"></i>
        </div>
        <h4 class="mt-2 mb-3" id="erroresTitulo">Errores encontrados:</h4>
        <ul class="list-unstyled" id="erroresLista"></ul>
        <button type="button" class="btn btn-danger mt-3" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de confirmación de edición/guardado -->
<div class="modal fade" id="modalConfirmarGuardar" tabindex="-1" aria-labelledby="modalGuardarLabel">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-body">
        <div style="font-size:3rem;color:#fd7e14;">
          <i class="fa fa-exclamation-circle"></i>
        </div>
        <h4 class="mt-2 mb-3">¿Está seguro de guardar los cambios?</h4>
        <button type="button" class="btn btn-primary me-2" id="btnSiGuardar">Sí</button>
        <button type="button" class="btn btn-danger ms-2" data-bs-dismiss="modal">NO</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-body">
        <div style="font-size:3rem;color:#dc3545;">
          <i class="fa fa-trash-alt"></i>
        </div>
        <h4 class="mt-2 mb-3">¿Está seguro de eliminar este elemento?</h4>
        <p class="text-muted">Esta acción no se puede deshacer</p>
        <button type="button" class="btn btn-danger me-2" id="btnSiEliminar">Sí, eliminar</button>
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de éxito -->
<div class="modal fade" id="modalExito" tabindex="-1" aria-labelledby="modalExitoLabel">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-body">
        <div style="font-size:3rem;color:#28a745;">
          <i class="fa fa-check-circle"></i>
        </div>
        <h4 class="mt-2 mb-3" id="exitoTitulo">¡Operación exitosa!</h4>
        <p id="exitoMensaje">La operación se completó correctamente</p>
        <button type="button" class="btn btn-success mt-3" data-bs-dismiss="modal">Aceptar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de información -->
<div class="modal fade" id="modalInfo" tabindex="-1" aria-labelledby="modalInfoLabel">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-body">
        <div style="font-size:3rem;color:#17a2b8;">
          <i class="fa fa-info-circle"></i>
        </div>
        <h4 class="mt-2 mb-3" id="infoTitulo">Información</h4>
        <p id="infoMensaje">Mensaje informativo</p>
        <button type="button" class="btn btn-info mt-3" data-bs-dismiss="modal">Entendido</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para mostrar QR -->
<div class="modal fade" id="modalQr" tabindex="-1" aria-labelledby="modalQrLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Código QR del Equipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qrContainer"></div>
                <p id="qrEquipoDatos" class="mt-3 text-start"></p>
            </div>
        </div>
    </div>
</div>