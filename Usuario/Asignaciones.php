<?php 
include('../navbar_unificado.php'); 
include('../conexion.php');
$conexion = new Conexion();

// Trae todos los equipos asignados y colaborador
$sql = "SELECT a.id, a.inventario_id, a.colaborador_id, a.fecha_asignacion, a.fecha_retiro, a.estado as estado_asignacion,
               c.id as colaborador_id, c.nombre as colaborador_nombre, c.foto as colaborador_foto,
               i.nombre_equipo, i.imagen as equipo_imagen, i.marca, i.modelo, i.numero_serie, i.costo, i.estado as estado_equipo,
               (SELECT estado FROM donaciones WHERE inventario_id = a.inventario_id AND estado = 'aprobada' LIMIT 1) as fue_donado
        FROM asignaciones a
        LEFT JOIN colaboradores c ON a.colaborador_id = c.id
        LEFT JOIN inventario i ON a.inventario_id = i.id
        WHERE a.estado = 'asignado'
        ORDER BY a.fecha_asignacion DESC";
$result = $conexion->getConexion()->query($sql);
?>

<div class="container mt-5">
    <h2>Asignaciones de equipos</h2>
    <div class="d-flex mb-3 justify-content-end">
        <a href="Inventario.php" class="btn btn-primary">Volver a Inventario</a>
    </div>
    <table class="table table-bordered table-striped mt-4" id="tabla-asignaciones-admin">
        <thead class="thead-dark">
            <tr>
                <th>Colaborador</th>
                <th>Equipo</th>
                <th>Imagen</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Serie</th>
                <th>Costo</th>
                <th>Estado equipo</th>
                <th>Fecha Asignación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $result->fetch_assoc()) {
                $foto = "../mostrar_foto_usuario.php?tipo=colaborador&id=" . $row['colaborador_id'];
                $imgEquipo = !empty($row['equipo_imagen']) ? "../uploads/{$row['equipo_imagen']}" : "../img/equipo.jpg";
                echo "<tr data-id='{$row['id']}' data-inventario='{$row['inventario_id']}'
                          data-colaborador='{$row['colaborador_id']}'
                          data-nombre='{$row['nombre_equipo']}'
                          data-marca='{$row['marca']}'
                          data-modelo='{$row['modelo']}'
                          data-serie='{$row['numero_serie']}'
                          data-costo='{$row['costo']}'
                          data-estado='{$row['estado_equipo']}'
                          data-fecha-asignacion='{$row['fecha_asignacion']}'
                          data-donado='{$row['fue_donado']}'>";
                echo "<td class='text-center'>
                        <img src='{$foto}' class='rounded-circle mb-1' width='48' height='48'><br>
                        <span>{$row['colaborador_nombre']}</span>
                     </td>
                     <td>{$row['nombre_equipo']}</td>
                     <td><img src='{$imgEquipo}' width='60'></td>
                     <td>{$row['marca']}</td>
                     <td>{$row['modelo']}</td>
                     <td>{$row['numero_serie']}</td>
                     <td>{$row['costo']}</td>
                     <td>{$row['estado_equipo']}</td>
                     <td>{$row['fecha_asignacion']}</td>
                     <td>";
                if ($row['fue_donado'] === 'aprobada') {
                    echo "<span class='text-success'>Equipo donado</span>";
                } else {
                    echo "<button class='btn btn-danger btn-sm btn-retirar' type='button'>Retirar</button>";
                }
                echo "</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Modal para retirar equipo -->
<div class="modal fade" id="modalRetirar" tabindex="-1" aria-labelledby="modalRetirarLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formRetirar">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRetirarLabel">Retirar equipo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p>¿Seguro que desea retirar el equipo <span id="nombreEquipoRetiro"></span> del colaborador?</p>
                    <div class="mb-3">
                        <label for="motivoRetiro" class="form-label">Motivo del retiro:</label>
                        <textarea id="motivoRetiro" name="motivo" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="nuevoEstado" class="form-label">Nuevo estado del equipo:</label>
                        <select id="nuevoEstado" name="nuevo_estado" class="form-control" required>
                            <option value="inventario">inventario</option>
                            <option value="reparacion">reparacion</option>
                            <option value="descarte">descarte</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Confirmar retiro</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
                <input type="hidden" name="asignacion_id" id="inputAsignacionId">
                <input type="hidden" name="inventario_id" id="inputInventarioId">
            </form>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script src="../js/retirarequipo.js"></script>