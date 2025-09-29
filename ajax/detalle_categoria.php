<?php
require_once '../conexion.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['categoria_id'])) {
        $categoria_id = intval($_POST['categoria_id']);
        
        if ($categoria_id <= 0) {
            throw new Exception('ID de categoría inválido');
        }
        
        $conexion = new Conexion();
        $equipos = $conexion->obtenerEquiposPorCategoria($categoria_id);
        
        if (empty($equipos)) {
            echo '<div class="alert alert-info">No hay equipos en esta categoría.</div>';
            exit;
        }
        ?>
        
        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Serie</th>
                        <th>Estado</th>
                        <th>Asignado a</th>
                        <th>Fecha Asignación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($equipos as $equipo): ?>
                    <tr>
                        <td><?= htmlspecialchars($equipo['id']) ?></td>
                        <td><?= htmlspecialchars($equipo['nombre_equipo']) ?></td>
                        <td><?= htmlspecialchars($equipo['marca']) ?></td>
                        <td><?= htmlspecialchars($equipo['modelo']) ?></td>
                        <td><?= htmlspecialchars($equipo['numero_serie']) ?></td>
                        <td>
                            <span class="badge <?= $equipo['estado_equipo'] === 'Disponible' ? 'bg-success' : 'bg-warning' ?>">
                                <?= htmlspecialchars($equipo['estado_equipo']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($equipo['asignado_a'] ?? '-') ?></td>
                        <td><?= $equipo['fecha_asignacion'] ? date('d/m/Y', strtotime($equipo['fecha_asignacion'])) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            <button class="btn btn-success" onclick="exportarCategoria(<?= $categoria_id ?>, '<?= htmlspecialchars($equipos[0]['categoria'] ?? 'Categoria') ?>')">
                <i class="fas fa-file-excel"></i> Exportar a Excel
            </button>
        </div>
        
        <?php
    } else {
        throw new Exception('Solicitud inválida - falta categoria_id');
    }
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
