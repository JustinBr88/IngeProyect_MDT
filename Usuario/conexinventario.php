<?php
require_once '../conexion.php';
header('Content-Type: application/json; charset=utf-8');
$conexion = new Conexion();
$db = $conexion->getConexion();

// helper para respuestas JSON consistentes
function jsonResp($success, $message = '', $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

// LIST BY LOTE - devuelve los equipos que tienen lote_id = ?
if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'list_by_lote') {
    $lote_id = isset($_GET['lote_id']) ? intval($_GET['lote_id']) : (isset($_POST['lote_id']) ? intval($_POST['lote_id']) : 0);
    if ($lote_id <= 0) {
        jsonResp(false, 'lote_id inválido', [], 400);
    }

    $stmt = $db->prepare("SELECT id, lote_id, equipo_num, nombre_equipo, categoria_id, marca, modelo, numero_serie, costo, fecha_ingreso, tiempo_depreciacion, estado, descripcion, imagen FROM inventario WHERE lote_id = ? ORDER BY equipo_num ASC, id ASC");
    if (!$stmt) {
        jsonResp(false, 'Error preparando consulta: ' . $db->error, null, 500);
    }
    $stmt->bind_param('i', $lote_id);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        jsonResp(false, 'Error ejecutando consulta: ' . $err, null, 500);
    }
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    jsonResp(true, 'Equipos del lote', $rows);
}

// EXISTE NUMERO DE SERIE? -> devuelve { success: true, data: { exists: true/false } }
if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'exists') {
    $numero = trim($_GET['numero_serie'] ?? $_REQUEST['numero_serie'] ?? '');
    if ($numero === '') jsonResp(false, 'numero_serie requerido', null, 400);
    $st = $db->prepare("SELECT id FROM inventario WHERE numero_serie = ? LIMIT 1");
    if (!$st) jsonResp(false, 'Error preparando consulta exists: ' . $db->error, null, 500);
    $st->bind_param('s', $numero);
    if (!$st->execute()) { $st->close(); jsonResp(false, 'Error ejecutando exists: ' . $st->error, null, 500); }
    $res = $st->get_result();
    $found = (bool)$res->fetch_assoc();
    $st->close();
    jsonResp(true, $found ? 'Existe' : 'No existe', ['exists' => $found]);
}

// A partir de aquí mantengo tus handlers existentes (UPDATE / DELETE / ALTA)
// UPDATE
if (isset($_GET['action']) && $_GET['action'] === 'update') {
    $data = json_decode(file_get_contents("php://input"), true);
    $errores = [];
    // Allow two modes:
    // 1) Minimal move: { id, lote_id } => only update lote_id
    // 2) Full update: provide other fields (legacy behaviour)

    if (!is_array($data)) $data = [];
    $id = intval($data['id'] ?? 0);
    if ($id <= 0) jsonResp(false, 'ID inválido', null, 400);

    // Case: only moving to another lote
    if (array_key_exists('lote_id', $data) && count($data) <= 2) {
        $newLote = intval($data['lote_id']);
        if ($newLote <= 0) jsonResp(false, 'Lote destino inválido', null, 400);

        // Verify that the destination lote exists
        $chk = $db->prepare("SELECT id FROM lotes WHERE id = ? LIMIT 1");
        if (!$chk) jsonResp(false, 'Error preparando verificación de lote: ' . $db->error, null, 500);
        $chk->bind_param('i', $newLote);
        if (!$chk->execute()) { $chk->close(); jsonResp(false, 'Error ejecutando verificación de lote: ' . $chk->error, null, 500); }
        $reschk = $chk->get_result();
        $exists = (bool)$reschk->fetch_assoc();
        $chk->close();
        if (!$exists) jsonResp(false, 'Lote destino no existe', null, 400);

        $stmt = $db->prepare("UPDATE inventario SET lote_id = ? WHERE id = ?");
        if (!$stmt) jsonResp(false, 'Error preparando UPDATE lote: ' . $db->error, null, 500);
        $stmt->bind_param('ii', $newLote, $id);
        if (!$stmt->execute()) { $stmt->close(); jsonResp(false, 'Error actualizando lote del equipo: ' . $stmt->error, null, 500); }
        $stmt->close();
        jsonResp(true, 'Equipo movido a lote ' . $newLote, ['id' => $id, 'lote_id' => $newLote]);
    }

    // Full update path (legacy): validate and update provided fields
    $errores = [];
    if (!isset($data['nombre_equipo']) || strlen(trim($data['nombre_equipo'])) < 2) {
        $errores[] = 'El nombre del equipo es obligatorio y debe tener al menos 2 caracteres.';
    }
    if (isset($data['categoria_id'])) {
        $catStmt = $db->prepare("SELECT id FROM categorias WHERE id = ?");
        $catStmt->bind_param("i", $data['categoria_id']);
        $catStmt->execute();
        $catStmt->store_result();
        if ($catStmt->num_rows === 0) { $errores[] = 'La categoría seleccionada no existe.'; }
        $catStmt->close();
    }
    if (isset($data['costo']) && (!is_numeric($data['costo']) || $data['costo'] < 0)) { $errores[] = 'El costo debe ser un número positivo.'; }
    if (isset($data['tiempo_depreciacion']) && (!preg_match('/^\d+$/', $data['tiempo_depreciacion']) || $data['tiempo_depreciacion'] < 0)) { $errores[] = 'La depreciación debe ser un número entero positivo.'; }
    if (isset($data['fecha_ingreso']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha_ingreso'])) { $errores[] = 'La fecha de ingreso debe tener formato AAAA-MM-DD.'; }
    $estados_validos = ['activo', 'baja', 'reparacion', 'descarte', 'donado', 'inventario', 'solicitado', 'asignado'];
    if (isset($data['estado']) && !in_array($data['estado'], $estados_validos)) { $errores[] = 'El estado seleccionado no es válido.'; }
    if ($errores) jsonResp(false, implode("\n", $errores), null, 400);

    $campos = ['nombre_equipo','categoria_id','marca','modelo','numero_serie','costo','fecha_ingreso','tiempo_depreciacion','estado','descripcion'];
    $sets = [];
    $params = [];
    $types = '';
    foreach ($campos as $campo) {
        if (array_key_exists($campo, $data)) { $sets[] = "$campo=?"; $params[] = $data[$campo]; $types .= 's'; }
    }
    if (!$sets) jsonResp(false, 'No se proporcionaron campos para actualizar', null, 400);
    $params[] = $id; $types .= 'i';
    $stmt = $db->prepare("UPDATE inventario SET " . implode(',', $sets) . " WHERE id=?");
    if (!$stmt) jsonResp(false, 'Error preparando UPDATE: '.$db->error, null, 500);
    // bind_param requires references — prepare parameters accordingly
    $stringParams = array_map('strval', $params);
    $bindNames = array_merge([$types], $stringParams);
    // convert to references
    $refs = [];
    foreach ($bindNames as $key => $value) { $refs[$key] = &$bindNames[$key]; }
    call_user_func_array([$stmt, 'bind_param'], $refs);
    $ok = $stmt->execute();
    $stmt->close();
    jsonResp((bool)$ok, $ok ? 'Equipo actualizado' : 'Error actualizando equipo', null, $ok ? 200 : 500);
    exit;
}

// DELETE
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    // Accept JSON body or form POST (compatibility with different fetch usages)
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    $id = 0;
    if (is_array($data) && isset($data['id'])) {
        $id = intval($data['id']);
    } elseif (isset($_POST['id'])) {
        $id = intval($_POST['id']);
    } elseif (isset($_REQUEST['id'])) {
        $id = intval($_REQUEST['id']);
    }
    if ($id <= 0) jsonResp(false, 'ID inválido', null, 400);
    $stmt = $db->prepare("DELETE FROM inventario WHERE id=?");
    if (!$stmt) jsonResp(false, 'Error preparando DELETE: ' . $db->error, null, 500);
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    jsonResp((bool)$success, $success ? 'Equipo eliminado' : 'Error al eliminar equipo');
    exit;
}

// ALTA (nuevo equipo/software)
if (isset($_GET['action']) && $_GET['action'] === 'alta') {
    $db = $conexion->getConexion();

    $lote_id = isset($_POST['lote_id']) && $_POST['lote_id'] !== '' ? intval($_POST['lote_id']) : null;
    $require_lote = isset($_POST['require_lote']) && ($_POST['require_lote'] === '1' || $_POST['require_lote'] === 'true');

    $numero_serie = trim($_POST['numero_serie'] ?? '');
    $modelo = trim($_POST['modelo'] ?? null);
    $fecha_ingreso = $_POST['fecha_ingreso'] ?? null;
    $estado = $_POST['estado'] ?? 'activo';
    $descripcion = trim($_POST['descripcion'] ?? null);

    if ($numero_serie === '') jsonResp(false, 'Número de serie obligatorio', null, 400);

    if ($require_lote && $lote_id === null) {
        jsonResp(false, 'Debe seleccionar un lote para guardar el equipo', null, 400);
    }

    if ($lote_id !== null) {
        $st = $db->prepare("SELECT id FROM lotes WHERE id = ?");
        if (!$st) jsonResp(false, 'Error preparando verificación de lote: ' . $db->error, null, 500);
        $st->bind_param('i', $lote_id);
        $st->execute();
        $r = $st->get_result();
        if (!$r->fetch_assoc()) jsonResp(false, 'Lote no existe', null, 400);
        $st->close();
    }

    // If lote_id provided and inherited fields not provided, fetch lote to inherit values
    $inherited = [];
    if ($lote_id !== null) {
        $st2 = $db->prepare("SELECT nombre_equipo, categoria_id, marca, costo, tiempo_depreciacion, imagen, descripcion FROM lotes WHERE id = ? LIMIT 1");
        if ($st2) {
            $st2->bind_param('i', $lote_id);
            $st2->execute();
            $res2 = $st2->get_result();
            $inherited = $res2->fetch_assoc() ?: [];
            $st2->close();
        }
    }

    // Fecha: si no se proporciona, usar fecha actual
    if (empty($fecha_ingreso)) $fecha_ingreso = date('Y-m-d');

    // Imagen opcional por unidad
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
        $imagenNombre = 'eq_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $destDir = __DIR__ . '/../uploads/';
        if (!is_dir($destDir)) mkdir($destDir, 0777, true);
        $dest = $destDir . $imagenNombre;
        if (!move_uploaded_file($f['tmp_name'], $dest)) jsonResp(false, 'Error guardando imagen', null, 500);
    }
    // If imagen provided as POST (inherited filename), accept it
    if ($imagenNombre === null && isset($_POST['imagen']) && $_POST['imagen'] !== '') {
        $imagenNombre = basename($_POST['imagen']);
    }

    // Build columns and values to allow inherited lote fields
    $cols = ['lote_id', 'numero_serie', 'modelo', 'fecha_ingreso', 'estado', 'descripcion', 'imagen'];
    $values = [$lote_id !== null ? $lote_id : null, $numero_serie, $modelo, $fecha_ingreso, $estado, $descripcion, $imagenNombre];

    // prefer explicit POST values, fall back to lote inherited values
    if (isset($_POST['nombre_equipo'])) { $cols[] = 'nombre_equipo'; $values[] = trim($_POST['nombre_equipo']); }
    elseif (!empty($inherited['nombre_equipo'])) { $cols[] = 'nombre_equipo'; $values[] = $inherited['nombre_equipo']; }

    if (isset($_POST['categoria_id']) && $_POST['categoria_id'] !== '') { $cols[] = 'categoria_id'; $values[] = intval($_POST['categoria_id']); }
    elseif (!empty($inherited['categoria_id'])) { $cols[] = 'categoria_id'; $values[] = intval($inherited['categoria_id']); }

    if (isset($_POST['marca'])) { $cols[] = 'marca'; $values[] = trim($_POST['marca']); }
    elseif (!empty($inherited['marca'])) { $cols[] = 'marca'; $values[] = $inherited['marca']; }

    if (isset($_POST['costo']) && $_POST['costo'] !== '') { $cols[] = 'costo'; $values[] = $_POST['costo']; }
    elseif (isset($inherited['costo'])) { $cols[] = 'costo'; $values[] = $inherited['costo']; }

    if (isset($_POST['tiempo_depreciacion']) && $_POST['tiempo_depreciacion'] !== '') { $cols[] = 'tiempo_depreciacion'; $values[] = $_POST['tiempo_depreciacion']; }
    elseif (isset($inherited['tiempo_depreciacion'])) { $cols[] = 'tiempo_depreciacion'; $values[] = $inherited['tiempo_depreciacion']; }

    if (!isset($_POST['descripcion']) && !empty($inherited['descripcion'])) { $cols[] = 'descripcion'; $values[] = $inherited['descripcion']; }

    $placeholders = implode(', ', array_fill(0, count($cols), '?'));
    $sql = "INSERT INTO inventario (" . implode(',', $cols) . ") VALUES ($placeholders)";
    $stmt = $db->prepare($sql);
    if (!$stmt) jsonResp(false, 'Error preparando insert: ' . $db->error, null, 500);

    // Use string binding for simplicity (MySQL will cast where needed)
    $types = str_repeat('s', count($cols));
    $bindNames = array_merge([$types], array_map(function($v){ return $v === null ? null : (string)$v; }, $values));
    $refs = [];
    foreach ($bindNames as $k => $v) { $refs[$k] = &$bindNames[$k]; }
    call_user_func_array([$stmt, 'bind_param'], $refs);

    if (!$stmt->execute()) {
        if ($db->errno === 1062) jsonResp(false, 'Número de serie ya existe', null, 400);
        jsonResp(false, 'Error insertando equipo: ' . $stmt->error, null, 500);
    }
    $newId = $stmt->insert_id;
    $stmt->close();
    jsonResp(true, 'Equipo creado', ['id' => $newId]);
}

jsonResp(false, 'Acción no encontrada', null, 400);
?>