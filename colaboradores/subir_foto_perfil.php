<?php
session_start();
require_once '../conexion.php';

// Verificar que el colaborador esté logueado
if (!isset($_SESSION['colaborador_id'])) {
    echo json_encode(['success' => false, 'message' => 'Colaborador no autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_perfil'])) {
    $colaborador_id = $_SESSION['colaborador_id'];
    $archivo = $_FILES['foto_perfil'];
    
    // Validar archivo
    $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $tamano_maximo = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($archivo['type'], $tipos_permitidos)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido. Solo se permiten JPG, PNG y GIF.']);
        exit;
    }
    
    if ($archivo['size'] > $tamano_maximo) {
        echo json_encode(['success' => false, 'message' => 'El archivo es demasiado grande. Máximo 5MB.']);
        exit;
    }
    
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Error al subir el archivo.']);
        exit;
    }
    
    try {
        $conexion = new Conexion();
        $mysqli = $conexion->getConexion();
        
        // Leer el archivo como datos binarios
        $foto_datos = file_get_contents($archivo['tmp_name']);
        $foto_tipo = $archivo['type'];
        
        // Actualizar la foto en la base de datos
        $stmt = $mysqli->prepare("UPDATE colaboradores SET foto = ?, foto_tipo = ? WHERE id = ?");
        $stmt->bind_param("ssi", $foto_datos, $foto_tipo, $colaborador_id);
        
        if ($stmt->execute()) {
            // Actualizar la sesión con la nueva foto
            $_SESSION['colaborador_foto'] = base64_encode($foto_datos);
            echo json_encode(['success' => true, 'message' => 'Foto de perfil actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar la foto en la base de datos']);
        }
        
        $stmt->close();
        $mysqli->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo']);
}
?>
