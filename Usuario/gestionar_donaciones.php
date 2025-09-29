<?php
include 'loginSesion.php';
include('../navbar_unificado.php');
require_once '../conexion.php';

$conexion = new Conexion();

// Procesar donación
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['procesar_donacion'])) {
    $donacion_id = $_POST['donacion_id'];
    $accion = $_POST['accion'];
    $usuario_admin_id = $_SESSION['id'];
    
    $resultado = $conexion->procesarDonacion($donacion_id, $usuario_admin_id, $accion);
    if ($resultado['success']) {
        $mensaje = "<div class='alert alert-success'>" . $resultado['message'] . "</div>";
    } else {
        $mensaje = "<div class='alert alert-danger'>" . $resultado['message'] . "</div>";
    }
}

// Obtener solicitudes de donación pendientes
$solicitudes_donacion = $conexion->obtenerSolicitudesDonacion();
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-heart"></i> Gestión de Donaciones</h2>
        <a href="Inventario.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Volver a Inventario
        </a>
    </div>
    
    <p class="text-muted">Revisa y gestiona las solicitudes de donación de equipos presentadas por los colaboradores.</p>
    
    <?= $mensaje ?>
    
    <?php if (empty($solicitudes_donacion)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No hay solicitudes de donación pendientes en este momento.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5><i class="fas fa-heart"></i> Solicitudes de Donación Pendientes (<?= count($solicitudes_donacion) ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Colaborador</th>
                                <th>Equipo</th>
                                <th>Imagen</th>
                                <th>Detalles</th>
                                <th>Destinatario</th>
                                <th>Fecha Solicitud</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($solicitudes_donacion as $donacion): ?>
                            <tr>
                                <td class="text-center">
                                    <?php 
                                    $foto_colaborador = "../mostrar_foto_usuario.php?tipo=colaborador&id=" . $donacion['colaborador_id'];
                                    ?>
                                    <img src="<?= htmlspecialchars($foto_colaborador) ?>" class="rounded-circle mb-1" width="40" height="40" alt="Foto colaborador"><br>
                                    <small><strong><?= htmlspecialchars($donacion['colaborador_nombre'] . ' ' . $donacion['colaborador_apellido']) ?></strong></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($donacion['nombre_equipo']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($donacion['categoria'] ?? 'Sin categoría') ?></small>
                                </td>
                                <td>
                                    <?php 
                                    $imagen_equipo = !empty($donacion['equipo_imagen']) ? "../uploads/" . $donacion['equipo_imagen'] : "../img/perfil.jpg";
                                    ?>
                                    <img src="<?= htmlspecialchars($imagen_equipo) ?>" width="50" height="50" class="rounded" alt="Imagen equipo">
                                </td>
                                <td>
                                    <strong>Marca:</strong> <?= htmlspecialchars($donacion['marca']) ?><br>
                                    <strong>Modelo:</strong> <?= htmlspecialchars($donacion['modelo']) ?><br>
                                    <strong>Serie:</strong> <?= htmlspecialchars($donacion['numero_serie']) ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($donacion['destinatario']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars(substr($donacion['motivo'], 0, 100)) ?>
                                    <?php if (strlen($donacion['motivo']) > 100): ?>...<?php endif; ?></small>
                                </td>
                                <td>
                                    <?= date('d/m/Y H:i', strtotime($donacion['fecha_donacion'])) ?>
                                </td>
                                <td>
                                    <button class="btn btn-success btn-sm btn-revisar" 
                                            data-donacion="<?= $donacion['id'] ?>"
                                            data-equipo="<?= htmlspecialchars($donacion['nombre_equipo']) ?>"
                                            data-colaborador="<?= htmlspecialchars($donacion['colaborador_nombre'] . ' ' . $donacion['colaborador_apellido']) ?>"
                                            data-destinatario="<?= htmlspecialchars($donacion['destinatario']) ?>"
                                            data-motivo="<?= htmlspecialchars($donacion['motivo']) ?>"
                                            data-marca="<?= htmlspecialchars($donacion['marca']) ?>"
                                            data-modelo="<?= htmlspecialchars($donacion['modelo']) ?>"
                                            data-serie="<?= htmlspecialchars($donacion['numero_serie']) ?>">
                                        <i class="fas fa-eye"></i> Revisar
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para revisar donación -->
<div class="modal fade" id="modalRevisarDonacion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-heart"></i> Revisar Solicitud de Donación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-primary">Información del Solicitante</h6>
                            <p><strong>Colaborador:</strong> <span id="nombreColaborador"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Información del Equipo</h6>
                            <p><strong>Equipo:</strong> <span id="nombreEquipo"></span></p>
                            <p><strong>Marca/Modelo:</strong> <span id="marcaModelo"></span></p>
                            <p><strong>Serie:</strong> <span id="serieEquipo"></span></p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-success">Destinatario de la Donación</h6>
                        <div id="destinatarioDonacion" class="border p-3 bg-light rounded"></div>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-success">Motivo y Justificación</h6>
                        <div id="motivoDonacion" class="border p-3 bg-light rounded"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="accion" class="form-label"><strong>Decisión *</strong></label>
                        <select name="accion" id="accion" class="form-select" required>
                            <option value="">Seleccione una acción</option>
                            <option value="aprobar">✅ Aprobar donación (equipo sale del inventario)</option>
                            <option value="rechazar">❌ Rechazar donación (equipo continúa asignado)</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Importante:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>Si apruebas:</strong> El equipo saldrá permanentemente del inventario y se considerará donado</li>
                            <li><strong>Si rechazas:</strong> El equipo continuará asignado al colaborador</li>
                            <li>Esta acción no se puede deshacer una vez procesada</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-success">
                        <i class="fas fa-heart"></i>
                        <strong>Impacto Social:</strong> Las donaciones contribuyen al desarrollo social y educativo de la comunidad.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="procesar_donacion" class="btn btn-success">
                        <i class="fas fa-heart"></i> Procesar Donación
                    </button>
                </div>
                <input type="hidden" name="donacion_id" id="inputDonacionId">
            </form>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar botones de revisar
    document.querySelectorAll('.btn-revisar').forEach(btn => {
        btn.addEventListener('click', function() {
            const donacionId = this.getAttribute('data-donacion');
            const equipo = this.getAttribute('data-equipo');
            const colaborador = this.getAttribute('data-colaborador');
            const destinatario = this.getAttribute('data-destinatario');
            const motivo = this.getAttribute('data-motivo');
            const marca = this.getAttribute('data-marca');
            const modelo = this.getAttribute('data-modelo');
            const serie = this.getAttribute('data-serie');
            
            document.getElementById('nombreColaborador').textContent = colaborador;
            document.getElementById('nombreEquipo').textContent = equipo;
            document.getElementById('marcaModelo').textContent = marca + ' ' + modelo;
            document.getElementById('serieEquipo').textContent = serie;
            document.getElementById('destinatarioDonacion').textContent = destinatario;
            document.getElementById('motivoDonacion').textContent = motivo;
            document.getElementById('inputDonacionId').value = donacionId;
            
            // Limpiar formulario
            document.getElementById('accion').value = '';
            
            new bootstrap.Modal(document.getElementById('modalRevisarDonacion')).show();
        });
    });
    
    // Validación del formulario
    document.querySelector('form').addEventListener('submit', function(e) {
        const accion = document.getElementById('accion').value;
        
        if (!accion) {
            e.preventDefault();
            alert('Por favor selecciona una acción (aprobar o rechazar)');
            return;
        }
        
        const mensaje = accion === 'aprobar' ? 
            '¿Estás seguro de aprobar esta donación? El equipo saldrá permanentemente del inventario.' :
            '¿Estás seguro de rechazar esta donación? El equipo continuará asignado al colaborador.';
            
        if (!confirm(mensaje)) {
            e.preventDefault();
        }
    });
});
</script>
