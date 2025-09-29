<?php
session_start();
require_once 'conexion.php';

// Headers para cache de imágenes
header('Cache-Control: public, max-age=3600'); // Cache por 1 hora
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime(__FILE__)) . ' GMT');

// Determinar qué tipo de usuario y ID usar
$usuario_id = null;
$colaborador_id = null;
$tipo = 'usuario'; // por defecto

// Verificar si se pasa un parámetro específico
if (isset($_GET['tipo'])) {
    $tipo = $_GET['tipo'];
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    if ($tipo === 'colaborador') {
        $colaborador_id = (int)$_GET['id'];
    } else {
        $usuario_id = (int)$_GET['id'];
    }
} else {
    // Usar datos de sesión
    if (isset($_SESSION['colaborador_id']) && isset($_SESSION['colaborador_logeado'])) {
        $colaborador_id = $_SESSION['colaborador_id'];
        $tipo = 'colaborador';
    } elseif (isset($_SESSION['id'])) {
        $usuario_id = $_SESSION['id'];
        $tipo = 'usuario';
    }
}

// Si no hay ID válido, mostrar imagen por defecto
if (!$usuario_id && !$colaborador_id) {
    $imagen_defecto_path = 'img/usuarios/default.jpg';
    if (file_exists($imagen_defecto_path) && is_readable($imagen_defecto_path)) {
        $imagen_defecto = file_get_contents($imagen_defecto_path);
        header('Content-Type: image/jpeg');
        echo $imagen_defecto;
    } else {
        // Si no existe default.jpg, crear una imagen SVG simple
        header('Content-Type: image/svg+xml');
        echo '<?xml version="1.0" encoding="UTF-8"?>
        <svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
            <rect width="150" height="150" fill="#e9ecef"/>
            <circle cx="75" cy="60" r="25" fill="#6c757d"/>
            <path d="M 45 100 Q 75 80 105 100 L 105 150 L 45 150 Z" fill="#6c757d"/>
        </svg>';
    }
    exit;
}

try {
    $conexion = new Conexion();
    $mysqli = $conexion->getConexion();
    
    if ($tipo === 'colaborador' && $colaborador_id) {
        // Obtener la foto del colaborador
        $stmt = $mysqli->prepare("SELECT foto, foto_tipo FROM colaboradores WHERE id = ?");
        $stmt->bind_param("i", $colaborador_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $colaborador = $result->fetch_assoc();
        
        if ($colaborador && $colaborador['foto']) {
            // Mostrar la foto desde la base de datos
            $tipo_mime = $colaborador['foto_tipo'] ?: 'image/jpeg';
            header('Content-Type: ' . $tipo_mime);
            echo $colaborador['foto'];
        } else {
            // Mostrar imagen por defecto si no tiene foto
            $imagen_defecto_path = 'img/usuarios/default.jpg';
            if (file_exists($imagen_defecto_path) && is_readable($imagen_defecto_path)) {
                $imagen_defecto = file_get_contents($imagen_defecto_path);
                header('Content-Type: image/jpeg');
                echo $imagen_defecto;
            } else {
                // Si no existe default.jpg, crear una imagen SVG simple
                header('Content-Type: image/svg+xml');
                echo '<?xml version="1.0" encoding="UTF-8"?>
                <svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
                    <rect width="150" height="150" fill="#e9ecef"/>
                    <circle cx="75" cy="60" r="25" fill="#6c757d"/>
                    <path d="M 45 100 Q 75 80 105 100 L 105 150 L 45 150 Z" fill="#6c757d"/>
                </svg>';
            }
        }
    } else {
        // Obtener la foto del usuario
        $stmt = $mysqli->prepare("SELECT foto, foto_tipo FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        
        if ($usuario && $usuario['foto']) {
            // Mostrar la foto desde la base de datos
            $tipo_mime = $usuario['foto_tipo'] ?: 'image/jpeg';
            header('Content-Type: ' . $tipo_mime);
            echo $usuario['foto'];
        } else {
            // Mostrar imagen por defecto si no tiene foto
            $imagen_defecto_path = 'img/usuarios/default.jpg';
            if (file_exists($imagen_defecto_path) && is_readable($imagen_defecto_path)) {
                $imagen_defecto = file_get_contents($imagen_defecto_path);
                header('Content-Type: image/jpeg');
                echo $imagen_defecto;
            } else {
                // Si no existe default.jpg, crear una imagen SVG simple
                header('Content-Type: image/svg+xml');
                echo '<?xml version="1.0" encoding="UTF-8"?>
                <svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
                    <rect width="150" height="150" fill="#e9ecef"/>
                    <circle cx="75" cy="60" r="25" fill="#6c757d"/>
                    <path d="M 45 100 Q 75 80 105 100 L 105 150 L 45 150 Z" fill="#6c757d"/>
                </svg>';
            }
        }
    }
    
} catch (Exception $e) {
    // En caso de error, mostrar imagen SVG por defecto
    header('Content-Type: image/svg+xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>
    <svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
        <rect width="150" height="150" fill="#e9ecef"/>
        <circle cx="75" cy="60" r="25" fill="#6c757d"/>
        <path d="M 45 100 Q 75 80 105 100 L 105 150 L 45 150 Z" fill="#6c757d"/>
    </svg>';
}
?>
