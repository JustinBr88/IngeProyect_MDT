<?php
// Configurar headers para JSON y CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
ini_set('display_errors', 0); // No mostrar errores en output
error_reporting(E_ALL);

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    require_once 'conexion.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario = $_POST['usuario'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';

        if (empty($usuario) || empty($contrasena)) {
            echo json_encode(['success' => false, 'mensaje' => 'Usuario y contraseña son requeridos']);
            exit;
        }

        try {
            $conexion = new Conexion();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
            exit;
        }

        // Primero intenta como usuario/admin
        try {
            $user = $conexion->validarUsuario($usuario, $contrasena);

            if ($user) {
                session_start();
                // Limpiar cualquier sesión de colaborador anterior
                unset($_SESSION['colaborador_logeado']);
                unset($_SESSION['colaborador_id']);
                unset($_SESSION['colaborador_nombre']);
                unset($_SESSION['colaborador_apellido']);
                unset($_SESSION['colaborador_foto']);
                unset($_SESSION['colaborador_usuario']);
                
                // Establecer variables de usuario/admin
                $_SESSION['usuario'] = $user['nombre'];
                $_SESSION['logeado'] = true;
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['id'] = $user['id']; // Agregar ID para verificación de roles
                $_SESSION['foto'] = $user['foto'] ?? '';
                $_SESSION['tipo'] = 'usuario';
                echo json_encode([
                    'success' => true, 
                    'mensaje' => 'Login exitoso como usuario',
                    'redirect' => 'Usuario/Home.php',
                    'tipo' => 'usuario'
                ]);
                exit;
            }
        } catch (Exception $e) {
            // Log el error pero continúa intentando con colaborador
            error_log("Error validando usuario: " . $e->getMessage());
        }

        // Luego intenta como colaborador
        try {
            $colab = $conexion->validarColaborador($usuario, $contrasena);

            if ($colab) {
                session_start();
                // Limpiar cualquier sesión de usuario anterior
                unset($_SESSION['logeado']);
                unset($_SESSION['usuario']);
                unset($_SESSION['rol']);
                unset($_SESSION['id']);
                unset($_SESSION['foto']);
                unset($_SESSION['tipo']);
                
                // Establecer solo variables de colaborador
                $_SESSION['colaborador_logeado'] = true;
                $_SESSION['colaborador_id'] = $colab['id'];
                $_SESSION['colaborador_nombre'] = $colab['nombre'];
                $_SESSION['colaborador_apellido'] = $colab['apellido'];
                $_SESSION['colaborador_foto'] = $colab['foto'] ?? '';
                $_SESSION['colaborador_usuario'] = $colab['usuario'];
                echo json_encode([
                    'success' => true, 
                    'mensaje' => 'Login exitoso como colaborador',
                    'redirect' => 'colaboradores/portal_colaborador.php',
                    'tipo' => 'colaboradores'
                ]);
                exit;
            }
        } catch (Exception $e) {
            error_log("Error validando colaborador: " . $e->getMessage());
        }

        // Si ninguno funciona
        echo json_encode(['success' => false, 'mensaje' => 'Usuario o contraseña incorrectos']);
    } else {
        echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}
?>