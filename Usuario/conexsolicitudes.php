<?php
require_once '../conexion.php';
session_start();
header('Content-Type: application/json');
$conexion = new Conexion();

// Verificar que el usuario sea administrador
if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    echo json_encode(['success' => false, 'error' => 'No tiene permisos de administrador.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$solicitud_id = $data['solicitud_id'] ?? null;
$accion = $data['accion'] ?? null;

if (!$solicitud_id || !$accion) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
    exit;
}

// Validar que la acción sea válida
if (!in_array($accion, ['aprobar', 'rechazar'])) {
    echo json_encode(['success' => false, 'error' => 'Acción no válida.']);
    exit;
}

// Iniciar transacción
$conexion->getConexion()->begin_transaction();

try {
    // Obtener datos de la solicitud
    $stmt = $conexion->getConexion()->prepare("
        SELECT s.inventario_id, s.colaborador_id, s.nombre_equipo, i.estado as estado_actual
        FROM solicitudes s 
        LEFT JOIN inventario i ON s.inventario_id = i.id 
        WHERE s.id = ? AND s.estado = 'pendiente'
    ");
    $stmt->bind_param("i", $solicitud_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Solicitud no encontrada o ya procesada.');
    }
    
    $solicitud = $result->fetch_assoc();
    $stmt->close();
    
    if ($accion === 'aprobar') {
        // 1. Cambiar estado del equipo a "asignado"
        $stmt = $conexion->getConexion()->prepare("
            UPDATE inventario SET estado = 'asignado' WHERE id = ?
        ");
        $stmt->bind_param("i", $solicitud['inventario_id']);
        $stmt->execute();
        $stmt->close();
        
        // 2. Crear registro en tabla asignaciones
        $stmt = $conexion->getConexion()->prepare("
            INSERT INTO asignaciones (inventario_id, colaborador_id, fecha_asignacion, estado) 
            VALUES (?, ?, NOW(), 'asignado')
        ");
        $stmt->bind_param("ii", $solicitud['inventario_id'], $solicitud['colaborador_id']);
        $stmt->execute();
        $stmt->close();
        
        // 3. Actualizar estado de la solicitud a "aprobada"
        $stmt = $conexion->getConexion()->prepare("
            UPDATE solicitudes SET estado = 'aprobada', fecha_respuesta = NOW() WHERE id = ?
        ");
        $stmt->bind_param("i", $solicitud_id);
        $stmt->execute();
        $stmt->close();
        
        $mensaje = 'Solicitud aprobada y equipo asignado correctamente.';
        
    } else { // rechazar
        // 1. Cambiar estado del equipo de vuelta a "inventario" o "activo"
        $nuevo_estado = ($solicitud['estado_actual'] === 'solicitado') ? 'inventario' : $solicitud['estado_actual'];
        $stmt = $conexion->getConexion()->prepare("
            UPDATE inventario SET estado = ? WHERE id = ?
        ");
        $stmt->bind_param("si", $nuevo_estado, $solicitud['inventario_id']);
        $stmt->execute();
        $stmt->close();
        
        // 2. Actualizar estado de la solicitud a "rechazada"
        $stmt = $conexion->getConexion()->prepare("
            UPDATE solicitudes SET estado = 'rechazada', fecha_respuesta = NOW() WHERE id = ?
        ");
        $stmt->bind_param("i", $solicitud_id);
        $stmt->execute();
        $stmt->close();
        
        $mensaje = 'Solicitud rechazada y equipo devuelto al inventario.';
    }
    
    // Confirmar transacción
    $conexion->getConexion()->commit();
    echo json_encode(['success' => true, 'message' => $mensaje]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conexion->getConexion()->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>