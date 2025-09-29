<?php
require_once '../conexion.php';
header('Content-Type: application/json');
$conexion = new Conexion();

// UPDATE
if ($_GET['action'] === 'update') {
    $data = json_decode(file_get_contents("php://input"), true);
    $errores = [];

    // Validaciones backend
    if (!isset($data['nombre_equipo']) || strlen($data['nombre_equipo']) < 2) {
        $errores[] = 'El nombre del equipo es obligatorio y debe tener al menos 2 caracteres.';
    }
    
    // Valida categoría contra la base de datos si se proporciona
    if (isset($data['categoria_id'])) {
        $catStmt = $conexion->getConexion()->prepare("SELECT id FROM categorias WHERE id = ?");
        $catStmt->bind_param("i", $data['categoria_id']);
        $catStmt->execute();
        $catStmt->store_result();
        if ($catStmt->num_rows === 0) {
            $errores[] = 'La categoría seleccionada no existe.';
        }
        $catStmt->close();
    }

    if (isset($data['costo']) && (!is_numeric($data['costo']) || $data['costo'] < 0)) {
        $errores[] = 'El costo debe ser un número positivo.';
    }
    
    if (isset($data['tiempo_depreciacion']) && (!preg_match('/^\d+$/', $data['tiempo_depreciacion']) || $data['tiempo_depreciacion'] < 0)) {
        $errores[] = 'La depreciación debe ser un número entero positivo.';
    }
    
    if (isset($data['fecha_ingreso']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha_ingreso'])) {
        $errores[] = 'La fecha de ingreso debe tener formato AAAA-MM-DD.';
    }
    
    // Restricción: no permitir editar a "solicitado" o "asignado" (solo si el valor original es uno de estos, bloquea desde JS)
    $estados_validos = ['activo', 'baja', 'reparacion', 'descarte', 'donado', 'inventario', 'solicitado', 'asignado'];
    if (isset($data['estado']) && !in_array($data['estado'], $estados_validos)) {
        $errores[] = 'El estado seleccionado no es válido.';
    }

    // Si hay errores, devolver error
    if ($errores) {
        echo json_encode(['success'=>false, 'error'=>implode("\n", $errores)]);
        exit;
    }

    // Proceder con la actualización si no hay errores
    $id = $data['id'];
    $campos = ['nombre_equipo','categoria_id','marca','modelo','numero_serie','costo','fecha_ingreso','tiempo_depreciacion','estado'];
    $sets = [];
    $params = [];
    $types = '';
    
    foreach ($campos as $campo) {
        if (isset($data[$campo])) {
            $sets[] = "$campo=?";
            $params[] = $data[$campo];
            $types .= (in_array($campo, ['costo','tiempo_depreciacion','categoria_id']) ? 'd' : 's');
        }
    }
    
    if ($sets) {
        $params[] = $id;
        $types .= 'i';
        $stmt = $conexion->getConexion()->prepare("UPDATE inventario SET ".implode(',', $sets)." WHERE id=?");
        $stmt->bind_param(str_replace('d', 's', $types), ...$params); // todo como string por seguridad
        $success = $stmt->execute();
        $stmt->close();
        echo json_encode(['success'=>$success]); 
        exit;
    }
    echo json_encode(['success'=>false, 'error'=>'No se proporcionaron campos para actualizar']);
    exit;
}

// DELETE
if ($_GET['action'] === 'delete') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];
    $stmt = $conexion->getConexion()->prepare("DELETE FROM inventario WHERE id=?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    echo json_encode(['success'=>$success]);
    exit;
}

// ALTA (nuevo equipo/software)
if ($_GET['action'] === 'alta') {
    $errores = [];
    
    // Validaciones para alta
    $nombre_equipo = trim($_POST['nombre_equipo'] ?? '');
    if (strlen($nombre_equipo) < 2) {
        $errores[] = 'El nombre del equipo es obligatorio y debe tener al menos 2 caracteres.';
    }
    
    $categoria_id = $_POST['categoria_id'] ?? '';
    if (empty($categoria_id) || $categoria_id === '' || $categoria_id === '0') {
        $errores[] = 'La categoría es obligatoria.';
    } else {
        // Validar que la categoría existe
        $catStmt = $conexion->getConexion()->prepare("SELECT id FROM categorias WHERE id = ?");
        $catStmt->bind_param("i", $categoria_id);
        $catStmt->execute();
        $catStmt->store_result();
        if ($catStmt->num_rows === 0) {
            $errores[] = 'La categoría seleccionada no existe.';
        }
        $catStmt->close();
    }
    
    $costo = $_POST['costo'] ?? '';
    if (!empty($costo) && (!is_numeric($costo) || $costo < 0)) {
        $errores[] = 'El costo debe ser un número positivo.';
    }
    
    $tiempo_depreciacion = $_POST['tiempo_depreciacion'] ?? '';
    if (!empty($tiempo_depreciacion) && (!preg_match('/^\d+$/', $tiempo_depreciacion) || $tiempo_depreciacion < 0)) {
        $errores[] = 'La depreciación debe ser un número entero positivo.';
    }
    
    $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';
    if (!empty($fecha_ingreso) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_ingreso)) {
        $errores[] = 'La fecha de ingreso debe tener formato AAAA-MM-DD.';
    }
    
    $estado = $_POST['estado'] ?? '';
    // En el ALTA: (no permitas solicitado/asignado)
    $estados_validos = ['activo', 'baja', 'reparacion', 'descarte', 'donado', 'inventario'];
    if (!empty($estado) && !in_array($estado, $estados_validos)) {
        $errores[] = 'El estado seleccionado no es válido.';
    }
    
    // Proceder con la inserción
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $numero_serie = $_POST['numero_serie'] ?? '';

    $imagen = "";
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        // VALIDACIÓN DE DIMENSIONES
        $img_info = getimagesize($_FILES['imagen']['tmp_name']);
        if ($img_info) {
            $ancho = $img_info[0];
            $alto = $img_info[1];
            if ($ancho > 2500 || $alto > 2500) {
                $errores[] = "La imagen es demasiado grande. El máximo permitido es 2500x2500 píxeles.";
            }
        }
        // Si no hay errores, procesa la imagen normalmente
        if (empty($errores)) {
            $target_dir = "../uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $filename = uniqid() . "_" . basename($_FILES["imagen"]["name"]);
            $target_file = $target_dir . $filename;
            if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
                $imagen = $filename;
            }
        }
    }

    // Si hay errores, devolver error
    if ($errores) {
        echo json_encode(['success'=>false, 'error'=>implode("\n", $errores)]);
        exit;
    }

    $stmt = $conexion->getConexion()->prepare(
        "INSERT INTO inventario (nombre_equipo, categoria_id, marca, modelo, numero_serie, costo, fecha_ingreso, tiempo_depreciacion, estado, imagen)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sisssdssss", $nombre_equipo, $categoria_id, $marca, $modelo, $numero_serie, $costo, $fecha_ingreso, $tiempo_depreciacion, $estado, $imagen);
    $success = $stmt->execute();
    $stmt->close();

    echo json_encode(['success'=>$success]);
    exit;
}
?>