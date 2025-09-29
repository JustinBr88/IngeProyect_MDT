<?php 
include 'loginSesionColaborador.php';
include('../navbar_unificado.php');
require_once(__DIR__ . '/../conexion.php');

$conexion = new Conexion();
$colaborador_id = $_SESSION['colaborador_id'];

// Obtener solicitudes del colaborador actual
$solicitudes = $conexion->obtenerSolicitudesColaborador($colaborador_id);
?>

<div class="container-fluid pt-4 px-4">
    <div class="bg-light text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0">Mis Solicitudes</h6>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevaSolicitudModal">
                    <i class="fa fa-plus me-2"></i>Nueva Solicitud
                </button>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">ID</th>
                        <th scope="col">Equipo Solicitado</th>
                        <th scope="col">Fecha Solicitud</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Respuesta Admin</th>
                        <th scope="col">Fecha Respuesta</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($solicitudes)): ?>
                        <?php foreach ($solicitudes as $solicitud): ?>
                            <tr>
                                <td><?php echo $solicitud['id']; ?></td>
                                <td><?php echo htmlspecialchars($solicitud['equipo_nombre']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])); ?></td>
                                <td>
                                    <?php
                                    $estado = $solicitud['estado'];
                                    $badge_class = $estado === 'pendiente' ? 'warning' : ($estado === 'aceptada' ? 'success' : 'danger');
                                    echo "<span class='badge bg-$badge_class'>" . ucfirst($estado) . "</span>";
                                    ?>
                                </td>
                                <td><?php echo $solicitud['admin_nombre'] ?? '-'; ?></td>
                                <td><?php echo $solicitud['fecha_respuesta'] ? date('d/m/Y H:i', strtotime($solicitud['fecha_respuesta'])) : '-'; ?></td>
                                <td>
                                    <?php if ($solicitud['estado'] === 'pendiente'): ?>
                                        <button class="btn btn-sm btn-danger" onclick="cancelarSolicitud(<?php echo $solicitud['id']; ?>)">
                                            <i class="fa fa-times"></i> Cancelar
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                <i class="fa fa-inbox fa-2x mb-3"></i><br>
                                No tienes solicitudes registradas
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nueva Solicitud -->
<div class="modal fade" id="nuevaSolicitudModal" tabindex="-1" aria-labelledby="nuevaSolicitudModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevaSolicitudModalLabel">Nueva Solicitud de Equipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevaSolicitud" method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="equipo_id" class="form-label">Seleccionar Equipo</label>
                        <select class="form-select" id="equipo_id" name="equipo_id" required>
                            <option value="">Seleccione un equipo...</option>
                            <?php 
                            $equipos_disponibles = $conexion->obtenerEquiposDisponibles();
                            foreach ($equipos_disponibles as $equipo): 
                            ?>
                                <option value="<?php echo $equipo['id']; ?>">
                                    <?php echo htmlspecialchars($equipo['nombre_equipo']) . ' - ' . htmlspecialchars($equipo['categoria']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="justificacion" class="form-label">Justificación</label>
                        <textarea class="form-control" id="justificacion" name="justificacion" rows="3" 
                                  placeholder="Explique por qué necesita este equipo..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="crear_solicitud" class="btn btn-primary">Enviar Solicitud</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Procesar nueva solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_solicitud'])) {
    $equipo_id = $_POST['equipo_id'];
    $justificacion = trim($_POST['justificacion']);
    
    if ($conexion->crearSolicitudColaborador($colaborador_id, $equipo_id, $justificacion)) {
        echo "<script>
            alert('Solicitud creada exitosamente');
            window.location.href = 'SolicitudesColab.php';
        </script>";
    } else {
        echo "<script>alert('Error al crear la solicitud');</script>";
    }
}
?>

<script>
function cancelarSolicitud(solicitudId) {
    if (confirm('¿Está seguro de que desea cancelar esta solicitud?')) {
        fetch('ajax/cancelar_solicitud.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                solicitud_id: solicitudId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Solicitud cancelada exitosamente');
                location.reload();
            } else {
                alert('Error al cancelar la solicitud: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
        });
    }
}
</script>

<?php include('footer.php'); ?>
