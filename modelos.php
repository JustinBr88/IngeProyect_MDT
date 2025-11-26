<?php
// modelos.php - modales (asegurar IDs usados por JS)
// Coloca este archivo en la ruta donde lo includes (include_once).
require_once __DIR__ . '/conexion.php';
$conexion = new Conexion();
$categorias = $conexion->obtenerCategorias();

// Obtener lotes para el select del modalNuevo y select inline
$lotes_select = [];
$stmt = $conexion->getConexion()->prepare("SELECT id, nombre_equipo FROM lotes ORDER BY nombre_equipo ASC");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    $lotes_select = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!-- Modal detalles (lista de equipos por lote) -->
<div class="modal fade" id="modalDetalles" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalles del Lote <span id="detallesLoteNombre"></span></h5>
        <div>
          <button type="button" id="btnAddEquipoModal" class="btn btn-primary btn-sm me-2">Añadir Equipo</button>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
      </div>
      <div class="modal-body">
        <div class="table-responsive mb-3">
          <table class="table table-sm" id="tableEquiposLote">
            <thead>
              <tr>
                <th>#</th><th>ID</th><th>Número de serie</th><th>Modelo</th><th>Marca</th><th>Estado</th><th>Imagen</th><th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tbodyEquiposLote">
              <tr><td colspan="8" class="text-center">No hay equipos para este lote.</td></tr>
            </tbody>
          </table>
        </div>

        <!-- Inline quick add (opcional) -->
        <div id="inlineAddEquipo" style="display:none; margin-top:12px; border-top:1px dashed #ddd; padding-top:12px;">
          <h6>Añadir equipo rápido (hereda datos del lote seleccionado)</h6>
          <form id="inlineAddEquipoForm" class="row g-2">
            <div class="col-md-4">
              <label class="form-label">Lote</label>
              <select id="inline_lote_id" name="lote_id" class="form-control" required>
                <option value="">Seleccione lote</option>
                <?php foreach ($lotes_select as $lt): ?>
                  <option value="<?= htmlspecialchars($lt['id']) ?>"><?= htmlspecialchars($lt['nombre_equipo']) ?> (ID: <?= htmlspecialchars($lt['id']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Número de serie *</label>
              <input id="inline_numero_serie" name="numero_serie" type="text" class="form-control" required pattern="[0-9]+" inputmode="numeric" title="Solo números" />
            </div>
            <div class="col-md-4">
              <label class="form-label">Modelo</label>
              <input id="inline_modelo" name="modelo" type="text" class="form-control" />
            </div>
            <div class="col-12 mt-2">
              <button type="submit" class="btn btn-sm btn-success">Crear equipo</button>
              <button type="button" id="inlineCancel" class="btn btn-sm btn-secondary">Cancelar</button>
            </div>
          </form>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Añadir / Editar Lote -->
<div class="modal fade" id="modalLote" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="formLote" class="modal-content" enctype="multipart/form-data">
      <input type="hidden" id="lote_id" name="id" value="">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLoteTitle">Nuevo Lote</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Nombre del lote *</label>
            <input type="text" name="nombre_equipo" id="nombre_equipo" class="form-control" required maxlength="100">
          </div>
            <!-- Nombre, categoría y demás campos del lote -->
          <div class="col-md-4">
            <label class="form-label">Categoría *</label>
            <select name="categoria_id" id="categoria_id" class="form-control" required>
              <option value="" disabled selected>-- Seleccione --</option>
              <?php if (!empty($categorias)): foreach ($categorias as $cat): ?>
                <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
              <?php endforeach; else: ?>
                <option value="" disabled>No hay categorías</option>
              <?php endif; ?>
            </select>
            <?php if (empty($categorias)): ?>
              <div class="form-text">No hay categorías. <a href="Usuario/Categorias.php" target="_blank">Crear categoría</a></div>
            <?php endif; ?>
          </div>

          <div class="col-md-4">
            <label class="form-label">Marca *</label>
            <input type="text" name="marca" id="marca" class="form-control" maxlength="50" required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Costo *</label>
            <input type="number" step="0.01" name="costo" id="costo" class="form-control" min="0" required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Tiempo de depreciación (meses) *</label>
            <input type="number" name="tiempo_depreciacion" id="tiempo_depreciacion" class="form-control" min="0" required>
          </div>

          <div class="col-12">
            <label class="form-label">Descripción *</label>
            <textarea name="descripcion" id="descripcion" class="form-control" rows="3" required></textarea>
          </div>

          <div class="col-md-6">
            <label class="form-label">Imagen (opcional)</label>
            <input type="file" name="imagen" id="imagen" class="form-control" accept="image/*">
            <div class="form-text">Formatos: JPG/PNG/WEBP. Máx 5MB.</div>
          </div>

          <div class="col-md-6 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="propagar" name="propagar" value="1">
              <label class="form-check-label" for="propagar">Propagar a equipos</label>
              <div class="form-text">Si se marca, al guardar los cambios se actualizarán los equipos del lote.</div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal para Añadir Nuevo Equipo (modalNuevo) - SOLO Lote + Número de serie + Modelo -->
<div class="modal fade" id="modalNuevo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formNuevo" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Añadir Equipo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="nuevo_lote_id" class="form-label">Asignar a Lote *</label>
          <select name="lote_id" id="nuevo_lote_id" class="form-control" required>
            <option value="">Seleccione un lote</option>
            <?php foreach ($lotes_select as $lt): ?>
              <option value="<?= htmlspecialchars($lt['id']) ?>"><?= htmlspecialchars($lt['nombre_equipo']) ?> (ID: <?= htmlspecialchars($lt['id']) ?>)</option>
            <?php endforeach; ?>
          </select>
          <div class="form-text">Se heredarán Nombre, Categoría, Marca, Costo, Depreciación e Imagen del lote seleccionado.</div>
        </div>

        <div class="mb-3 row">
          <div class="col-md-6">
            <label for="nuevo_numero_serie" class="form-label">Número de serie *</label>
            <input type="text" name="numero_serie" id="nuevo_numero_serie" class="form-control" required pattern="[0-9]+" inputmode="numeric" title="Solo números">
          </div>
          <div class="col-md-6">
            <label for="nuevo_modelo" class="form-label">Modelo</label>
            <input type="text" name="modelo" id="nuevo_modelo" class="form-control">
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Guardar equipo</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>