<?php 
require_once '../vendor/autoload.php';
include('../navbar_unificado.php'); 
include('../conexion.php');
$conexion = new Conexion();

// Obtener todas las solicitudes pendientes, con datos de colaborador y equipo y motivo
$sql = "SELECT s.id, s.inventario_id, s.nombre_equipo, s.fecha_solicitud, s.estado, s.motivo,
               c.id as colaborador_id, c.nombre as colaborador_nombre, c.foto as colaborador_foto,
               i.imagen as equipo_imagen, i.categoria_id, cat.nombre as categoria_nombre, i.marca, i.modelo, i.numero_serie, i.costo, i.fecha_ingreso, i.tiempo_depreciacion, i.estado as equipo_estado
        FROM solicitudes s
        LEFT JOIN colaboradores c ON s.colaborador_id = c.id
        LEFT JOIN inventario i ON s.inventario_id = i.id
        LEFT JOIN categorias cat ON i.categoria_id = cat.id
        WHERE s.estado = 'pendiente'
        ORDER BY s.fecha_solicitud DESC";
$result = $conexion->getConexion()->query($sql);
?>

<div class="container mt-5">
    <h2>Solicitudes de equipos - Administración</h2>
    <div class="d-flex mb-3 justify-content-end">
        <a href="Inventario.php" class="btn btn-primary me-2">Volver a Inventario</a>
        <a href="Asignaciones.php" class="btn btn-info">Ver Asignaciones</a>
    </div>
    <table class="table table-bordered table-striped mt-4" id="tabla-solicitudes-admin">
        <thead class="thead-dark">
            <tr>
                <th>Colaborador</th>
                <th>Equipo</th>
                <th>Imagen</th>
                <th>Categoría</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Serie</th>
                <th>Costo</th>
                <th>Ingreso</th>
                <th>Depreciación</th>
                <th>Motivo</th>
                <th>QR</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $result->fetch_assoc()) {
                // Foto del colaborador usando el nuevo sistema
                $foto = "../mostrar_foto_usuario.php?tipo=colaborador&id=" . $row['colaborador_id'];
                // Imagen del equipo
                $imgEquipo = !empty($row['equipo_imagen']) ? "../uploads/{$row['equipo_imagen']}" : "../img/equipo.jpg";
                $categoriaNombre = !empty($row['categoria_nombre']) ? htmlspecialchars($row['categoria_nombre']) : 'Sin categoría';
                $motivo = nl2br(htmlspecialchars($row['motivo'] ?? ''));
                echo "<tr data-id='{$row['id']}' data-inventario='{$row['inventario_id']}'
                          data-nombre='".htmlspecialchars($row['nombre_equipo'], ENT_QUOTES)."' 
                          data-categoria='".htmlspecialchars($categoriaNombre, ENT_QUOTES)."'
                          data-marca='".htmlspecialchars($row['marca'], ENT_QUOTES)."'
                          data-modelo='".htmlspecialchars($row['modelo'], ENT_QUOTES)."'
                          data-serie='".htmlspecialchars($row['numero_serie'], ENT_QUOTES)."'
                          data-costo='".htmlspecialchars($row['costo'], ENT_QUOTES)."'
                          data-ingreso='".htmlspecialchars($row['fecha_ingreso'], ENT_QUOTES)."'
                          data-depreciacion='".htmlspecialchars($row['tiempo_depreciacion'], ENT_QUOTES)."'>
                    <td class='text-center'>
                        <img src='{$foto}' class='rounded-circle mb-1' width='48' height='48' alt='Foto colaborador'><br>
                        <span>{$row['colaborador_nombre']}</span>
                    </td>
                    <td>{$row['nombre_equipo']}</td>
                    <td><img src='{$imgEquipo}' width='60'></td>
                    <td>{$categoriaNombre}</td>
                    <td>{$row['marca']}</td>
                    <td>{$row['modelo']}</td>
                    <td>{$row['numero_serie']}</td>
                    <td>{$row['costo']}</td>
                    <td>{$row['fecha_ingreso']}</td>
                    <td>{$row['tiempo_depreciacion']}</td>
                    <td style='max-width:240px; white-space:normal;'>{$motivo}</td>
                    <td>
                        <button class='btn btn-success btn-sm btn-qr' type='button'>QR</button>
                    </td>
                    <td>
                        <button class='btn btn-success btn-sm btn-aprobar me-1' type='button'>Aprobar</button>
                        <button class='btn btn-danger btn-sm btn-rechazar' type='button'>Rechazar</button>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Modal para mostrar QR -->
<div class="modal fade" id="modalQr" tabindex="-1" aria-labelledby="modalQrLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalQrLabel">Código QR del equipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="qrContainer" class="text-center"></div>
                <div id="qrEquipoDatos" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de acción -->
<div class="modal fade" id="modalAccion" tabindex="-1" aria-labelledby="modalAccionLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formAccion">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAccionLabel">Confirmar acción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p id="accionTexto"></p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" id="btnConfirmarAccion">Confirmar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
                <input type="hidden" name="solicitud_id" id="inputSolicitudId">
                <input type="hidden" name="accion" id="inputAccion">
            </form>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script src="../js/adminSoli.js"></script>