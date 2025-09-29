<?php
session_start();
require_once '../conexion.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['id'];
    $password_actual = $_POST['password_actual'] ?? '';
    $password_nueva = $_POST['password_nueva'] ?? '';
    $password_confirmar = $_POST['password_confirmar'] ?? '';
    
    // Validaciones
    if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
        exit;
    }
    
    if ($password_nueva !== $password_confirmar) {
        echo json_encode(['success' => false, 'message' => 'Las nuevas contraseñas no coinciden']);
        exit;
    }
    
    if (strlen($password_nueva) < 6) {
        echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres']);
        exit;
    }
    
    try {
        $conexion = new Conexion();
        $mysqli = $conexion->getConexion();
        
        // Verificar contraseña actual
        $stmt = $mysqli->prepare("SELECT contrasena FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        
        if (!$usuario) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            exit;
        }
        
        // Verificar contraseña actual
        if (!password_verify($password_actual, $usuario['contrasena'])) {
            echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
            exit;
        }
        
        // Hashear nueva contraseña
        $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
        
        // Actualizar contraseña
        $stmt = $mysqli->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?");
        $stmt->bind_param("si", $password_hash, $usuario_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña']);
        }
        
        $stmt->close();
        $mysqli->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
