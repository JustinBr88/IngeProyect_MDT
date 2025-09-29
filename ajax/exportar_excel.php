<?php
require_once '../conexion.php';

// Función para generar Excel en formato más compatible
function generarExcel($datos, $nombreArchivo, $encabezados) {
    // Configurar headers para Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.xls"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');
    
    // Generar contenido HTML para Excel
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
    echo '<style>';
    echo 'table { border-collapse: collapse; width: 100%; }';
    echo 'th, td { border: 1px solid #dddddd; text-align: left; padding: 8px; }';
    echo 'th { background-color: #4CAF50; color: white; font-weight: bold; }';
    echo 'tr:nth-child(even) { background-color: #f2f2f2; }';
    echo '</style>';
    echo '</head>';
    echo '<body>';
    echo '<table>';
    
    // Escribir encabezados
    echo '<tr>';
    foreach ($encabezados as $encabezado) {
        echo '<th>' . htmlspecialchars($encabezado) . '</th>';
    }
    echo '</tr>';
    
    // Escribir datos
    foreach ($datos as $fila) {
        echo '<tr>';
        foreach ($fila as $celda) {
            // Limpiar y formatear el contenido
            $contenido = htmlspecialchars($celda ?? '');
            echo '<td>' . $contenido . '</td>';
        }
        echo '</tr>';
    }
    
    echo '</table>';
    echo '</body>';
    echo '</html>';
    
    exit;
}

// Función alternativa para generar CSV limpio
function generarCSV($datos, $nombreArchivo, $encabezados) {
    // Configurar headers para CSV
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // BOM para UTF-8 (para que Excel abra correctamente los caracteres especiales)
    echo "\xEF\xBB\xBF";
    
    // Crear salida CSV
    $output = fopen('php://output', 'w');
    
    // Escribir encabezados
    fputcsv($output, $encabezados, ',', '"');
    
    // Escribir datos
    foreach ($datos as $fila) {
        // Limpiar datos antes de escribir
        $filaLimpia = array_map(function($item) {
            return $item ?? '';
        }, $fila);
        fputcsv($output, $filaLimpia, ',', '"');
    }
    
    fclose($output);
    exit;
}

// Función para exportar según el formato
function exportarDatos($datos, $nombreArchivo, $encabezados, $formato) {
    if ($formato === 'csv') {
        generarCSV($datos, $nombreArchivo, $encabezados);
    } else {
        generarExcel($datos, $nombreArchivo, $encabezados);
    }
}

$conexion = new Conexion();
$tipo = $_GET['tipo'] ?? '';
$formato = $_GET['formato'] ?? 'excel'; // Por defecto Excel

switch ($tipo) {
    case 'inventario':
        // Exportar todo el inventario
        $inventario = $conexion->obtenerInventario();
        $datos = [];
        $encabezados = ['ID', 'Nombre Equipo', 'Categoría', 'Marca', 'Modelo', 'Número Serie', 'Estado', 'Fecha Ingreso'];
        
        foreach ($inventario as $item) {
            // Verificar si está asignado
            $asignaciones = $conexion->obtenerAsignaciones();
            $estado = 'Disponible';
            foreach ($asignaciones as $asig) {
                if ($asig['inventario_id'] == $item['id'] && $asig['estado'] == 'asignado') {
                    $estado = 'Asignado';
                    break;
                }
            }
            
            $datos[] = [
                $item['id'],
                $item['nombre_equipo'],
                $item['categoria'] ?? 'Sin categoría',
                $item['marca'],
                $item['modelo'],
                $item['numero_serie'],
                $estado,
                $item['fecha_ingreso'] ? date('d/m/Y', strtotime($item['fecha_ingreso'])) : '-'
            ];
        }
        
        exportarDatos($datos, 'Inventario_Completo_' . date('Y-m-d'), $encabezados, $formato);
        break;
        
    case 'asignaciones':
        // Exportar todas las asignaciones
        $asignaciones = $conexion->obtenerAsignaciones();
        $datos = [];
        $encabezados = ['ID Asignación', 'Equipo', 'Colaborador', 'Fecha Asignación', 'Estado', 'Observaciones'];
        
        foreach ($asignaciones as $asig) {
            $datos[] = [
                $asig['id'],
                $asig['nombre_equipo'] ?? 'Equipo eliminado',
                ($asig['colaborador_nombre'] ?? 'N/A') . ' ' . ($asig['colaborador_apellido'] ?? ''),
                date('d/m/Y', strtotime($asig['fecha_asignacion'])),
                ucfirst($asig['estado']),
                $asig['observaciones'] ?? ''
            ];
        }
        
        exportarDatos($datos, 'Asignaciones_' . date('Y-m-d'), $encabezados, $formato);
        break;
        
    case 'categoria':
        // Exportar equipos de una categoría específica
        $categoria_id = intval($_GET['categoria_id'] ?? 0);
        $categoria_nombre = $_GET['categoria'] ?? 'Categoria';
        
        $equipos = $conexion->obtenerEquiposPorCategoria($categoria_id);
        $datos = [];
        $encabezados = ['ID', 'Nombre Equipo', 'Marca', 'Modelo', 'Número Serie', 'Estado', 'Asignado a', 'Fecha Asignación'];
        
        foreach ($equipos as $equipo) {
            $datos[] = [
                $equipo['id'],
                $equipo['nombre_equipo'],
                $equipo['marca'],
                $equipo['modelo'],
                $equipo['numero_serie'],
                $equipo['estado_equipo'],
                $equipo['asignado_a'] ?? '-',
                $equipo['fecha_asignacion'] ? date('d/m/Y', strtotime($equipo['fecha_asignacion'])) : '-'
            ];
        }
        
        exportarDatos($datos, 'Categoria_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $categoria_nombre) . '_' . date('Y-m-d'), $encabezados, $formato);
        break;
        
    case 'filtrado':
        // Exportar reporte filtrado
        $categoria_id = !empty($_GET['categoria']) ? intval($_GET['categoria']) : null;
        $estado = $_GET['estado'] ?? null;
        $fecha_desde = $_GET['fecha_desde'] ?? null;
        $fecha_hasta = $_GET['fecha_hasta'] ?? null;
        
        $reporte = $conexion->obtenerReporteFiltrado($categoria_id, $estado, $fecha_desde, $fecha_hasta);
        $datos = [];
        $encabezados = ['ID', 'Nombre Equipo', 'Categoría', 'Marca', 'Modelo', 'Número Serie', 'Estado', 'Asignado a', 'Fecha Asignación', 'Fecha Ingreso'];
        
        foreach ($reporte as $item) {
            $datos[] = [
                $item['id'],
                $item['nombre_equipo'],
                $item['categoria'],
                $item['marca'],
                $item['modelo'],
                $item['numero_serie'],
                $item['estado_equipo'],
                $item['asignado_a'] ?? '-',
                $item['fecha_asignacion'] ? date('d/m/Y', strtotime($item['fecha_asignacion'])) : '-',
                $item['fecha_ingreso'] ? date('d/m/Y', strtotime($item['fecha_ingreso'])) : '-'
            ];
        }
        
        $nombre_archivo = 'Reporte_Filtrado_' . date('Y-m-d');
        if ($categoria_id) {
            $nombre_archivo .= '_Cat' . $categoria_id;
        }
        if ($estado) {
            $nombre_archivo .= '_' . ucfirst($estado);
        }
        
        exportarDatos($datos, $nombre_archivo, $encabezados, $formato);
        break;
        
    case 'estadisticas':
        // Exportar estadísticas por categoría
        $estadisticas = $conexion->obtenerEstadisticasPorCategoria();
        $datos = [];
        $encabezados = ['Categoría', 'Total Equipos', 'Equipos Asignados', 'Equipos Disponibles', '% Utilización'];
        
        foreach ($estadisticas as $stat) {
            $disponibles = $stat['total_equipos'] - $stat['equipos_asignados'];
            $porcentaje = $stat['total_equipos'] > 0 ? 
                round(($stat['equipos_asignados'] / $stat['total_equipos']) * 100, 1) : 0;
            
            $datos[] = [
                $stat['categoria'],
                $stat['total_equipos'],
                $stat['equipos_asignados'],
                $disponibles,
                $porcentaje . '%'
            ];
        }
        
        exportarDatos($datos, 'Estadisticas_por_Categoria_' . date('Y-m-d'), $encabezados, $formato);
        break;
        
    default:
        // Tipo no válido
        header('HTTP/1.1 400 Bad Request');
        echo 'Tipo de exportación no válido';
        exit;
}
?>
