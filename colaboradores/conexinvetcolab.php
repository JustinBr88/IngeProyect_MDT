<?php
require_once '../conexion.php';
session_start();
header('Content-Type: application/json');
$conexion = new Conexion();

// CORRECCIÓN: Verificar que el usuario sea COLABORADOR, no administrador
if (!isset($_SESSION['colaborador_id'])) {
    echo json_encode(['success' => false, 'error' => 'No has iniciado sesión como colaborador.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$inventario_id = $data['inventario_id'] ?? null;
$nombre_equipo = $data['nombre_equipo'] ?? null;
$motivo = $data['motivo'] ?? null;

// Validar datos requeridos
if (!$inventario_id || !$motivo) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
    exit;
}

// Limpiar y validar el motivo
$motivo = trim($motivo);
if (strlen($motivo) < 10) {
    echo json_encode(['success' => false, 'error' => 'El motivo debe tener al menos 10 caracteres.']);
    exit;
}

// Iniciar transacción
$conexion->getConexion()->begin_transaction();

try {
    // Verificar que el inventario exista y esté disponible
    $stmt = $conexion->getConexion()->prepare("SELECT estado, nombre_equipo FROM inventario WHERE id = ?");
    $stmt->bind_param("i", $inventario_id);
    $stmt->execute();
    $stmt->bind_result($estado, $nombre_equipo_db);
    
    if ($stmt->fetch()) {
        if ($estado !== 'activo' && $estado !== 'inventario') {
            throw new Exception('El equipo no está disponible para solicitud.');
        }
        $nombre_equipo = $nombre_equipo_db;
    } else {
        throw new Exception('Equipo no encontrado.');
    }
    $stmt->close();

    // Verificar si el colaborador ya tiene una solicitud pendiente para este equipo
    $stmt = $conexion->getConexion()->prepare("
        SELECT id FROM solicitudes 
        WHERE colaborador_id = ? AND inventario_id = ? AND estado = 'pendiente'
    ");
    $stmt->bind_param("ii", $_SESSION['colaborador_id'], $inventario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        throw new Exception('Ya tienes una solicitud pendiente para este equipo.');
    }
    $stmt->close();

    // 1. IMPORTANTE: Cambiar estado del equipo a "solicitado" para bloquear edición
    $stmt = $conexion->getConexion()->prepare("
        UPDATE inventario SET estado = 'solicitado' WHERE id = ?
    ");
    $stmt->bind_param("i", $inventario_id);
    $stmt->execute();
    $stmt->close();

    // 2. Insertar la solicitud
    $colaborador_id = $_SESSION['colaborador_id'];
    $fecha_solicitud = date('Y-m-d H:i:s');
    $estado_solicitud = 'pendiente';
    $tipo = 'asignacion';

    $stmt = $conexion->getConexion()->prepare("
        INSERT INTO solicitudes (inventario_id, nombre_equipo, colaborador_id, fecha_solicitud, estado, motivo, tipo) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isissss", $inventario_id, $nombre_equipo, $colaborador_id, $fecha_solicitud, $estado_solicitud, $motivo, $tipo);
    $stmt->execute();
    $stmt->close();

    // Confirmar transacción
    $conexion->getConexion()->commit();
    echo json_encode([
        'success' => true, 
        'message' => 'Solicitud enviada correctamente. El equipo está ahora marcado como solicitado.'
    ]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conexion->getConexion()->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>