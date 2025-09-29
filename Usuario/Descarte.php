<?php 
include 'loginSesion.php';
include('../navbar_unificado.php'); 
require_once '../conexion.php';

try {
    $conexion = new Conexion();
    
    // Obtener equipos activos para mostrar en la lista de descarte
    $equiposActivos = $conexion->obtenerInventario();
    
    // Obtener equipos en descarte
    $equiposDescarte = $conexion->obtenerEquiposDescarte();
    
} catch (Exception $e) {
    echo '<div class="container mt-5">
            <div class="alert alert-danger">
                <h4>Error al cargar datos</h4>
                <p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>
            </div>
          </div>';
    include('footer.php');
    exit;
}
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-trash-alt"></i> Gestión de Descartes</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMarcarDescarte">
            <i class="fas fa-plus"></i> Marcar Equipo como Descarte
        </button>
    </div>

    <!-- Equipos en Descarte -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4><i class="fas fa-exclamation-triangle"></i> Equipos en Descarte (<?= count($equiposDescarte) ?>)</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($equiposDescarte)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No hay equipos marcados como descarte actualmente.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Equipo</th>
                                        <th>Categoría</th>
                                        <th>Marca/Modelo</th>
                                        <th>Fecha Descarte</th>
                                        <th>Usuario</th>
                                        <th>Observaciones</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($equiposDescarte as $equipo): ?>
                                    <tr>
                                        <td><?= $equipo['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($equipo['nombre_equipo']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($equipo['numero_serie']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($equipo['categoria'] ?? 'Sin categoría') ?></span>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($equipo['marca']) ?><br>
                                            <small class="text-muted"><?= htmlspecialchars($equipo['modelo']) ?></small>
                                        </td>
                                        <td>
                                            <small><?= date('d/m/Y H:i', strtotime($equipo['fecha_descarte'])) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($equipo['tecnico_descarte']) ?></td>
                                        <td>
                                            <div class="observaciones-cortas" style="max-width: 200px;">
                                                <?= htmlspecialchars(substr($equipo['observaciones_descarte'], 0, 100)) ?>
                                                <?php if (strlen($equipo['observaciones_descarte']) > 100): ?>
                                                    <a href="#" onclick="verDetalleDescarte(<?= $equipo['id'] ?>)" class="text-primary">...ver más</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-info" onclick="verDetalleDescarte(<?= $equipo['id'] ?>)">
                                                    <i class="fas fa-eye"></i> Ver
                                                </button>
                                                <button class="btn btn-sm btn-success" onclick="restaurarEquipo(<?= $equipo['id'] ?>)">
                                                    <i class="fas fa-undo"></i> Restaurar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Marcar Equipo como Descarte -->
<div class="modal fade" id="modalMarcarDescarte" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Marcar Equipo como Descarte</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formDescarte">
                    <div class="mb-3">
                        <label class="form-label">Seleccionar Equipo:</label>
                        <select class="form-select" id="equipoDescarte" required>
                            <option value="">Seleccione un equipo...</option>
                            <?php foreach($equiposActivos as $equipo): ?>
                                <?php if ($equipo['estado_descarte'] !== 'descarte'): ?>
                                <option value="<?= $equipo['id'] ?>">
                                    <?= htmlspecialchars($equipo['nombre_equipo']) ?> - 
                                    <?= htmlspecialchars($equipo['numero_serie']) ?> - 
                                    <?= htmlspecialchars($equipo['marca']) ?> <?= htmlspecialchars($equipo['modelo']) ?>
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Técnico Responsable:</label>
                        <input type="text" class="form-control" id="tecnicoDescarte" 
                               value="<?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Observaciones Técnicas (Obligatorio):</label>
                        <textarea class="form-control" id="observacionesDescarte" rows="5" 
                                  placeholder="Describa detalladamente por qué considera que este equipo debe ser descartado: estado físico, fallas, daños irreparables, obsolescencia, etc." 
                                  required></textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> 
                            Proporcione una evaluación técnica completa del estado del equipo y las razones del descarte.
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atención:</strong> Al marcar un equipo como descarte:
                        <ul class="mb-0 mt-2">
                            <li>Si está asignado, será liberado automáticamente</li>
                            <li>Quedará marcado como "no disponible" para asignaciones</li>
                            <li>Podrá ser restaurado posteriormente si es necesario</li>
                        </ul>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarDescarte()">
                    <i class="fas fa-trash-alt"></i> Marcar como Descarte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Ver Detalle de Descarte -->
<div class="modal fade" id="modalDetalleDescarte" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Detalle de Descarte</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoDetalleDescarte">
                <!-- Se cargará dinámicamente -->
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script src="../js/descarte.js"></script>
