<?php
require_once '../conexion.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$conexion = new Conexion();
$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'marcar_descarte':
        $inventario_id = intval($_POST['inventario_id'] ?? 0);
        $observaciones = trim($_POST['observaciones'] ?? '');
        $tecnico = trim($_POST['tecnico'] ?? '');
        
        if ($inventario_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de equipo inválido']);
            exit;
        }
        
        if (empty($observaciones)) {
            echo json_encode(['success' => false, 'message' => 'Las observaciones son obligatorias']);
            exit;
        }
        
        if (empty($tecnico)) {
            echo json_encode(['success' => false, 'message' => 'El nombre del técnico es obligatorio']);
            exit;
        }
        
        $resultado = $conexion->marcarDescarte($inventario_id, $observaciones, $tecnico);
        echo json_encode($resultado);
        break;
        
    case 'restaurar_descarte':
        $inventario_id = intval($_POST['inventario_id'] ?? 0);
        
        if ($inventario_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de equipo inválido']);
            exit;
        }
        
        $resultado = $conexion->restaurarDescarte($inventario_id);
        echo json_encode($resultado);
        break;
        
    case 'obtener_equipos_descarte':
        $equipos = $conexion->obtenerEquiposDescarte();
        echo json_encode(['success' => true, 'equipos' => $equipos]);
        break;
        
    case 'obtener_detalle_descarte':
        $inventario_id = intval($_POST['inventario_id'] ?? 0);
        
        if ($inventario_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de equipo inválido']);
            exit;
        }
        
        $detalle = $conexion->obtenerDetalleDescarte($inventario_id);
        if ($detalle) {
            echo json_encode(['success' => true, 'detalle' => $detalle]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Equipo no encontrado en descarte']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}
?>
