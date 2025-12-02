<?php
// Usuario/conexlotes.php
header('Content-Type: application/json; charset=utf-8');
// iniciar output buffering para poder limpiar cualquier salida accidental
if (!ob_get_level()) ob_start();
// evitar mostrar errores en pantalla
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');
// bandera global para indicar que ya enviamos JSON
$__conexlotes_json_sent = false;

// shutdown handler para atrapar errores fatales y devolver JSON limpio
register_shutdown_function(function() use (&$__conexlotes_json_sent) {
    $err = error_get_last();
    if ($err && !$__conexlotes_json_sent) {
        // registrar detalles del error para depuración
        try { error_log('[conexlotes.php] shutdown error: ' . print_r($err, true)); } catch (
            Exception $e) {}
        while (ob_get_level() > 0) ob_end_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Fatal error en el servidor al procesar la solicitud. Revisa logs.','data'=>null]);
    }
});

session_start();

require_once __DIR__ . '/../conexion.php'; 
$conexion = new Conexion();
$mysqli = $conexion->getConexion(); 

function jsonResp($ok, $msg = '', $data = null, $code = 200) {

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    // indicar al shutdown handler que ya enviamos respuesta JSON
    global $__conexlotes_json_sent;
    $__conexlotes_json_sent = true;

    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');

    $payload = ['success' => (bool)$ok, 'message' => $msg, 'data' => $data];
    echo json_encode($payload);
    exit;
}

// Permisos: comprobar si usuario es admin usando el método de la clase Conexion (si existe sesión)
$isAdmin = false;
if (isset($_SESSION['id'])) {
    try {
        $isAdmin = $conexion->esAdministrador((int)$_SESSION['id']);
    } catch (Exception $e) {
        $isAdmin = false;
    }
}

$action = $_REQUEST['action'] ?? 'list';

try {
    if ($action === 'list') {
        $stmt = $mysqli->prepare("SELECT id, nombre_equipo, categoria_id, marca, costo, tiempo_depreciacion, imagen, descripcion, cantidad, created_at, updated_at FROM lotes ORDER BY id DESC");
        if (!$stmt) jsonResp(false, 'Error preparando consulta: ' . $mysqli->error, null, 500);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        jsonResp(true, 'Lotes obtenidos', $rows);
    }

    if ($action === 'get') {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) jsonResp(false, 'ID inválido', null, 400);
        $stmt = $mysqli->prepare("SELECT id, nombre_equipo, categoria_id, marca, costo, tiempo_depreciacion, imagen, descripcion, cantidad, created_at, updated_at FROM lotes WHERE id = ?");
        if (!$stmt) jsonResp(false, 'Error preparando consulta: ' . $mysqli->error, null, 500);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        if (!$row) jsonResp(false, 'Lote no encontrado', null, 404);
        jsonResp(true, 'Lote encontrado', $row);
    }

    if ($action === 'save') {
        if (!$isAdmin) jsonResp(false, 'No autorizado', null, 403);

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $nombre = trim($_POST['nombre_equipo'] ?? '');
        $categoria_id = isset($_POST['categoria_id']) && $_POST['categoria_id'] !== '' ? intval($_POST['categoria_id']) : null;
        $marca = trim($_POST['marca'] ?? null);
        $costo = isset($_POST['costo']) && $_POST['costo'] !== '' ? floatval($_POST['costo']) : null;
        $tiempo_depreciacion = isset($_POST['tiempo_depreciacion']) && $_POST['tiempo_depreciacion'] !== '' ? intval($_POST['tiempo_depreciacion']) : null;
        $descripcion = trim($_POST['descripcion'] ?? null);
        $cantidad = isset($_POST['cantidad']) ? max(0, intval($_POST['cantidad'])) : null;
        $propagar = isset($_POST['propagar']) && ($_POST['propagar'] === '1' || $_POST['propagar'] === 'true');

        if ($nombre === '') jsonResp(false, 'El nombre del lote es obligatorio', null, 400);
        // Si estamos creando (id == 0) exigir todos los campos excepto imagen.
        if ($id <= 0) {
            if ($categoria_id === null || $categoria_id === 0) jsonResp(false, 'La categoría es obligatoria', null, 400);
            if ($marca === null || trim($marca) === '') jsonResp(false, 'La marca es obligatoria', null, 400);
            if ($costo === null || !is_numeric($costo) || $costo < 0) jsonResp(false, 'El costo es obligatorio y debe ser un número >= 0', null, 400);
            if ($tiempo_depreciacion === null || !is_numeric($tiempo_depreciacion) || $tiempo_depreciacion < 0) jsonResp(false, 'El tiempo de depreciación es obligatorio y debe ser >= 0', null, 400);
            if ($descripcion === null || trim($descripcion) === '') jsonResp(false, 'La descripción es obligatoria', null, 400);
        } else {
            // En actualización, validar solo tipos si se enviaron valores (permitir edición parcial)
            if ($categoria_id !== null && $categoria_id <= 0) jsonResp(false, 'Categoría inválida', null, 400);
            if ($costo !== null && (!is_numeric($costo) || $costo < 0)) jsonResp(false, 'El costo debe ser un número >= 0', null, 400);
            if ($tiempo_depreciacion !== null && (!is_numeric($tiempo_depreciacion) || $tiempo_depreciacion < 0)) jsonResp(false, 'El tiempo de depreciación debe ser >= 0', null, 400);
        }

        // Imagen opcional
        $imagenNombre = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
            $f = $_FILES['imagen'];
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg', 'image/gif'];
            if ($f['size'] > 5 * 1024 * 1024) jsonResp(false, 'Imagen demasiado grande (máx 5MB)', null, 400);
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $f['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, $allowed)) jsonResp(false, "Tipo de imagen no permitido: $mime", null, 400);
            $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
            $imagenNombre = 'lote_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $destDir = __DIR__ . '/../uploads/';
            if (!is_dir($destDir)) mkdir($destDir, 0777, true);
            $dest = $destDir . $imagenNombre;
            if (!move_uploaded_file($f['tmp_name'], $dest)) jsonResp(false, 'Error guardando imagen', null, 500);
        }

        $mysqli->begin_transaction();

        if ($id > 0) {
            $params = [];
            $types = '';
            $sets = [];
            $sets[] = 'nombre_equipo = ?'; $types .= 's'; $params[] = $nombre;
            $sets[] = 'categoria_id = ?'; $types .= 'i'; $params[] = $categoria_id;
            $sets[] = 'marca = ?'; $types .= 's'; $params[] = $marca;
            $sets[] = 'costo = ?'; $types .= 'd'; $params[] = $costo;
            $sets[] = 'tiempo_depreciacion = ?'; $types .= 'i'; $params[] = $tiempo_depreciacion;
            $sets[] = 'descripcion = ?'; $types .= 's'; $params[] = $descripcion;
            if ($cantidad !== null) { $sets[] = 'cantidad = ?'; $types .= 'i'; $params[] = $cantidad; }
            if ($imagenNombre !== null) { $sets[] = 'imagen = ?'; $types .= 's'; $params[] = $imagenNombre; }

            $sql = "UPDATE lotes SET " . implode(', ', $sets) . " WHERE id = ?";
            $types .= 'i'; $params[] = $id;

            $stmt = $mysqli->prepare($sql);
            if ($stmt === false) { $mysqli->rollback(); jsonResp(false, 'Error prepare UPDATE lote: ' . $mysqli->error, null, 500); }

            // bind dynamically
            $stmt->bind_param($types, ...$params);
            if (!$stmt->execute()) { $mysqli->rollback(); jsonResp(false, 'Error ejecutando UPDATE lote: ' . $stmt->error, null, 500); }

            if ($propagar) {
                $sqlp = "UPDATE inventario SET nombre_equipo = ?, categoria_id = ?, marca = ?, costo = ?, tiempo_depreciacion = ?, imagen = ?, descripcion = ? WHERE lote_id = ?";
                $stmtp = $mysqli->prepare($sqlp);
                if ($stmtp === false) { $mysqli->rollback(); jsonResp(false, 'Error prepare propagar: ' . $mysqli->error, null, 500); }
                $imgForProp = $imagenNombre ?? '';
                $stmtp->bind_param('sississi', $nombre, $categoria_id, $marca, $costo, $tiempo_depreciacion, $imgForProp, $descripcion, $id);
                if (!$stmtp->execute()) { $mysqli->rollback(); jsonResp(false, 'Error propagar a inventario: ' . $stmtp->error, null, 500); }
            }

            $mysqli->commit();
            jsonResp(true, 'Lote actualizado', ['id' => $id]);
        } else {
            $cantidadToUse = $cantidad !== null ? $cantidad : 0;
            $stmt = $mysqli->prepare("INSERT INTO lotes (nombre_equipo, categoria_id, marca, costo, tiempo_depreciacion, imagen, descripcion, cantidad) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt === false) { $mysqli->rollback(); jsonResp(false, 'Error prepare INSERT lote: ' . $mysqli->error, null, 500); }
            $stmt->bind_param('sisdsssi', $nombre, $categoria_id, $marca, $costo, $tiempo_depreciacion, $imagenNombre, $descripcion, $cantidadToUse);
            if (!$stmt->execute()) { $mysqli->rollback(); jsonResp(false, 'Error ejecutando INSERT lote: ' . $stmt->error, null, 500); }
            $newId = $stmt->insert_id;
            $stmt->close();
            $mysqli->commit();
            jsonResp(true, 'Lote creado', ['id' => $newId]);
        }
    }

    if ($action === 'delete') {
        if (!$isAdmin) jsonResp(false, 'No autorizado', null, 403);
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) jsonResp(false, 'ID inválido', null, 400);
        $stmt = $mysqli->prepare("SELECT cantidad, imagen FROM lotes WHERE id = ?");
        if (!$stmt) jsonResp(false, 'Error preparando consulta: ' . $mysqli->error, null, 500);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        if (!$row) jsonResp(false, 'Lote no encontrado', null, 404);
        if (intval($row['cantidad']) > 0) jsonResp(false, 'No se puede eliminar un lote con equipos. Vacía el lote primero.', null, 400);

        $mysqli->begin_transaction();
        $stmt = $mysqli->prepare("DELETE FROM lotes WHERE id = ?");
        if (!$stmt) { $mysqli->rollback(); jsonResp(false, 'Error preparando delete: ' . $mysqli->error, null, 500); }
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) { $mysqli->rollback(); jsonResp(false, 'Error al eliminar lote: ' . $stmt->error, null, 500); }
        if (!empty($row['imagen'])) {
            $path = __DIR__ . '/../uploads/' . $row['imagen'];
            if (file_exists($path)) @unlink($path);
        }
        $mysqli->commit();
        jsonResp(true, 'Lote eliminado');
    }

    jsonResp(false, 'Acción no reconocida', null, 400);
} catch (Exception $ex) {
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        @$mysqli->rollback();
    }
    jsonResp(false, 'Error interno: ' . $ex->getMessage(), null, 500);
}