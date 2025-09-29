<?php
require_once '../conexion.php';

try {
    $conexion = new Conexion();

    $categoria_id = !empty($_GET['categoria']) ? intval($_GET['categoria']) : null;
    $estado = $_GET['estado'] ?? null;
    $fecha_desde = $_GET['fecha_desde'] ?? null;
    $fecha_hasta = $_GET['fecha_hasta'] ?? null;

    // Obtener estadísticas filtradas
    if ($categoria_id || $estado || $fecha_desde || $fecha_hasta) {
        // Aplicar filtros personalizados
        $reporte = $conexion->obtenerReporteFiltrado($categoria_id, $estado, $fecha_desde, $fecha_hasta);
        
        // Agrupar por categoría
        $estadisticas_filtradas = [];
        foreach ($reporte as $item) {
            $cat = $item['categoria'];
            if (!isset($estadisticas_filtradas[$cat])) {
                $estadisticas_filtradas[$cat] = [
                    'categoria' => $cat,
                    'total_equipos' => 0,
                    'equipos_asignados' => 0,
                    'categoria_id' => null // Agregar campo para almacenar ID
                ];
            }
            $estadisticas_filtradas[$cat]['total_equipos']++;
            if ($item['estado_equipo'] === 'Asignado') {
                $estadisticas_filtradas[$cat]['equipos_asignados']++;
            }
        }
        
        $estadisticas = array_values($estadisticas_filtradas);
        
        // Obtener IDs de categorías para las acciones
        $todas_categorias = $conexion->obtenerCategorias();
        foreach ($estadisticas as &$stat) {
            foreach ($todas_categorias as $cat) {
                if ($cat['nombre'] === $stat['categoria']) {
                    $stat['categoria_id'] = $cat['id'];
                    break;
                }
            }
        }
    } else {
        // Sin filtros, mostrar todas las estadísticas
        $estadisticas = $conexion->obtenerEstadisticasPorCategoria();
    }

    // Verificar si hay datos
    if (empty($estadisticas)) {
        echo '<tr><td colspan="6" class="text-center">No se encontraron datos con los filtros aplicados</td></tr>';
        exit;
    }

    // Generar HTML para la tabla
    foreach($estadisticas as $stat): 
        $porcentaje = $stat['total_equipos'] > 0 ? 
            round(($stat['equipos_asignados'] / $stat['total_equipos']) * 100, 1) : 0;
        $disponibles = $stat['total_equipos'] - $stat['equipos_asignados'];
        
        // Usar el ID de categoría que ya está en el array
        $categoria_id_actual = $stat['categoria_id'] ?? null;
    ?>
    <tr>
        <td><?= htmlspecialchars($stat['categoria']) ?></td>
        <td>
            <span class="badge bg-primary"><?= $stat['total_equipos'] ?></span>
        </td>
        <td>
            <span class="badge bg-success"><?= $disponibles ?></span>
        </td>
        <td>
            <span class="badge bg-warning"><?= $stat['equipos_asignados'] ?></span>
        </td>
        <td>
            <div class="progress">
                <div class="progress-bar" role="progressbar" 
                     style="width: <?= $porcentaje ?>%" 
                     aria-valuenow="<?= $porcentaje ?>" 
                     aria-valuemin="0" aria-valuemax="100">
                    <?= $porcentaje ?>%
                </div>
            </div>
        </td>
        <td>
            <?php if ($categoria_id_actual): ?>
            <button class="btn btn-sm btn-info" 
                    onclick="verDetalle('<?= $categoria_id_actual ?>', '<?= htmlspecialchars($stat['categoria']) ?>')">
                Ver Detalle
            </button>
            <button class="btn btn-sm btn-success" 
                    onclick="exportarCategoria('<?= $categoria_id_actual ?>', '<?= htmlspecialchars($stat['categoria']) ?>')">
                Excel
            </button>
            <?php else: ?>
            <span class="text-muted">Sin acciones</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; 

} catch (Exception $e) {
    echo '<tr><td colspan="6" class="text-center text-danger">Error al procesar filtros: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
}
?>
