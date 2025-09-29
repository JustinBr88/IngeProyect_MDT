<?php
class Conexion {
    private $servername = "localhost";
    private $username = "root";
    private $password = "1234";
    private $dbname = "cmdb";
    private $port = 3306;
    private $conn;

    public function __construct() {
        try {
            // Opción 1: Conexión con configuración específica para WAMP
            $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
            
            if ($this->conn->connect_error) {
                throw new Exception("Error de conexión: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            // Opción 2: Intentar con 127.0.0.1 en lugar de localhost
            try {
                $this->conn = new mysqli("127.0.0.1", $this->username, $this->password, $this->dbname, $this->port);
                if ($this->conn->connect_error) {
                    throw new Exception("Error de conexión: " . $this->conn->connect_error);
                }
                $this->conn->set_charset("utf8mb4");
                
            } catch (Exception $e2) {
                // Opción 3: Intentar con socket local (para WAMP)
                try {
                    $this->conn = new mysqli("localhost:/tmp/mysql.sock", $this->username, $this->password, $this->dbname);
                    if ($this->conn->connect_error) {
                        throw new Exception("Error de conexión: " . $this->conn->connect_error);
                    }
                    $this->conn->set_charset("utf8mb4");
                } catch (Exception $e3) {
                    die("No se pudo establecer conexión a la base de datos. Posibles soluciones:<br>
                         1. Verifique que MySQL esté ejecutándose en WAMP<br>
                         2. Verifique que la contraseña del usuario root sea '1234'<br>
                         3. En phpMyAdmin, cambie el plugin de autenticación del usuario root a 'mysql_native_password'<br>
                         Error: " . $e3->getMessage());
                }
            }
        }
    }

    public function getConexion() {
        return $this->conn;
    }

    // Validación de usuario/admin por correo o usuario
    public function validarUsuario($usuario_correo, $contrasena) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE (nombre = ? OR correo = ?) AND rol = 'admin' AND activo = 1");
        $stmt->bind_param("ss", $usuario_correo, $usuario_correo);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario_db = $result->fetch_assoc();
        $stmt->close();
        if ($usuario_db && password_verify($contrasena, $usuario_db['contrasena'])) {
            return $usuario_db;
        }
        return false;
    }

    // Validación de colaborador por correo o usuario
    public function validarColaborador($usuario_correo, $contrasena) {
        // Validar en tabla usuarios con rol 'colab'
        $stmt = $this->conn->prepare("SELECT u.*, c.id as colaborador_id, c.nombre as colab_nombre, c.apellido, c.foto as colab_foto 
                                     FROM usuarios u 
                                     LEFT JOIN colaboradores c ON u.correo = c.correo 
                                     WHERE (u.nombre = ? OR u.correo = ?) AND u.rol = 'colab' AND u.activo = 1");
        $stmt->bind_param("ss", $usuario_correo, $usuario_correo);
        $stmt->execute();
        $result = $stmt->get_result();
        $colaborador = $result->fetch_assoc();
        $stmt->close();
        
        if ($colaborador && password_verify($contrasena, $colaborador['contrasena'])) {
            // Combinar datos de ambas tablas
            $colaborador['nombre'] = $colaborador['colab_nombre'] ?? $colaborador['nombre'];
            $colaborador['foto'] = $colaborador['colab_foto'] ?? $colaborador['foto'];
            $colaborador['usuario'] = $colaborador['nombre']; // Para compatibilidad
            return $colaborador;
        }
        return false;
    }

    // Obtener colaborador por ID
    public function obtenerColaboradorPorId($id) {
        $stmt = $this->conn->prepare("
            SELECT c.*, d.nombre as departamento_nombre, u.correo as usuario_correo, u.nombre as usuario_nombre
            FROM colaboradores c 
            LEFT JOIN departamentos d ON c.departamento_id = d.id
            LEFT JOIN usuarios u ON c.correo = u.correo AND u.rol = 'colab'
            WHERE c.id = ? AND c.activo = 1
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $colaborador = $result->fetch_assoc();
        $stmt->close();
        return $colaborador;
    }

    // Registrar acceso de colaborador
    public function registrarAccesoColaborador($colaborador_id) {
        $stmt = $this->conn->prepare("
            INSERT INTO historial_accesos_colaborador (colaborador_id, fecha_hora, ip, user_agent) 
            VALUES (?, NOW(), ?, ?)
        ");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $stmt->bind_param("iss", $colaborador_id, $ip, $user_agent);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Verificar si un correo ya existe para otro colaborador
    public function correoDuplicadoColaborador($correo, $colaborador_id) {
        $stmt = $this->conn->prepare("SELECT id FROM colaboradores WHERE correo = ? AND id != ? AND activo = 1");
        $stmt->bind_param("si", $correo, $colaborador_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existe = $result->num_rows > 0;
        $stmt->close();
        return $existe;
    }

    // Actualizar perfil de colaborador
    public function actualizarPerfilColaborador($id, $nombre, $apellido, $correo, $telefono, $direccion, $ubicacion, $foto_path = null) {
        if ($foto_path) {
            $stmt = $this->conn->prepare("
                UPDATE colaboradores 
                SET nombre = ?, apellido = ?, correo = ?, telefono = ?, direccion = ?, ubicacion = ?, foto = ?
                WHERE id = ?
            ");
            $stmt->bind_param("sssssssi", $nombre, $apellido, $correo, $telefono, $direccion, $ubicacion, $foto_path, $id);
        } else {
            $stmt = $this->conn->prepare("
                UPDATE colaboradores 
                SET nombre = ?, apellido = ?, correo = ?, telefono = ?, direccion = ?, ubicacion = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssssssi", $nombre, $apellido, $correo, $telefono, $direccion, $ubicacion, $id);
        }
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Obtener todos los colaboradores
    public function obtenerColaboradores() {
        $stmt = $this->conn->prepare("
            SELECT c.*, d.nombre as departamento_nombre 
            FROM colaboradores c 
            LEFT JOIN departamentos d ON c.departamento_id = d.id 
            WHERE c.activo = 1
            ORDER BY c.nombre, c.apellido
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $colaboradores = [];
        while ($row = $result->fetch_assoc()) {
            $colaboradores[] = $row;
        }
        $stmt->close();
        return $colaboradores;
    }

    // Obtener solicitudes de un colaborador específico
    public function obtenerSolicitudesColaborador($colaborador_id) {
        $stmt = $this->conn->prepare("
            SELECT s.*, i.nombre_equipo as equipo_nombre, c.nombre as categoria,
                   u.nombre as admin_nombre
            FROM solicitudes s
            LEFT JOIN inventario i ON s.inventario_id = i.id
            LEFT JOIN categorias c ON i.categoria_id = c.id
            LEFT JOIN usuarios u ON s.usuario_admin_id = u.id
            WHERE s.colaborador_id = ?
            ORDER BY s.fecha_solicitud DESC
        ");
        $stmt->bind_param("i", $colaborador_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $solicitudes = [];
        while ($row = $result->fetch_assoc()) {
            $solicitudes[] = $row;
        }
        $stmt->close();
        return $solicitudes;
    }

    // Obtener equipos disponibles para solicitud
    public function obtenerEquiposDisponibles() {
        $stmt = $this->conn->prepare("
            SELECT i.id, i.nombre_equipo, c.nombre as categoria
            FROM inventario i
            LEFT JOIN categorias c ON i.categoria_id = c.id
            WHERE i.estado = 'activo'
            ORDER BY i.nombre_equipo
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $equipos = [];
        while ($row = $result->fetch_assoc()) {
            $equipos[] = $row;
        }
        $stmt->close();
        return $equipos;
    }

    // Crear nueva solicitud de colaborador
    public function crearSolicitudColaborador($colaborador_id, $inventario_id, $justificacion = null) {
        $stmt = $this->conn->prepare("
            INSERT INTO solicitudes (colaborador_id, inventario_id, fecha_solicitud, estado, justificacion)
            VALUES (?, ?, NOW(), 'pendiente', ?)
        ");
        $stmt->bind_param("iis", $colaborador_id, $inventario_id, $justificacion);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Obtener estadísticas de colaborador
    public function obtenerEstadisticasColaborador($colaborador_id) {
        $stats = [];
        
        // Solicitudes pendientes
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM solicitudes WHERE colaborador_id = ? AND estado = 'pendiente'");
        $stmt->bind_param("i", $colaborador_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['solicitudes_pendientes'] = $result->fetch_assoc()['total'];
        $stmt->close();
        
        // Solicitudes aprobadas
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM solicitudes WHERE colaborador_id = ? AND estado = 'aceptada'");
        $stmt->bind_param("i", $colaborador_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['solicitudes_aprobadas'] = $result->fetch_assoc()['total'];
        $stmt->close();
        
        // Equipos asignados
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM asignaciones WHERE colaborador_id = ? AND estado = 'asignado'");
        $stmt->bind_param("i", $colaborador_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['equipos_asignados'] = $result->fetch_assoc()['total'];
        $stmt->close();
        
        // Entregas realizadas (funcionalidad eliminada - establecer en 0)
        $stats['entregas_realizadas'] = 0;
        
        return $stats;
    }

    // Verificar contraseña de colaborador
    public function verificarPasswordColaborador($colaborador_id, $password) {
        // Obtener el correo del colaborador
        $stmt = $this->conn->prepare("SELECT correo FROM colaboradores WHERE id = ?");
        $stmt->bind_param("i", $colaborador_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $colaborador = $result->fetch_assoc();
        $stmt->close();
        
        if (!$colaborador) return false;
        
        // Verificar password en tabla usuarios
        $stmt = $this->conn->prepare("SELECT contrasena FROM usuarios WHERE correo = ? AND rol = 'colab'");
        $stmt->bind_param("s", $colaborador['correo']);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $stmt->close();
        
        if (!$usuario) return false;
        
        return password_verify($password, $usuario['contrasena']);
    }

    // Cambiar contraseña de colaborador
    public function cambiarPasswordColaborador($colaborador_id, $nueva_password) {
        // Obtener el correo del colaborador
        $stmt = $this->conn->prepare("SELECT correo FROM colaboradores WHERE id = ?");
        $stmt->bind_param("i", $colaborador_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $colaborador = $result->fetch_assoc();
        $stmt->close();
        
        if (!$colaborador) return false;
        
        // Actualizar password en tabla usuarios
        $hashed_password = password_hash($nueva_password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE usuarios SET contrasena = ? WHERE correo = ? AND rol = 'colab'");
        $stmt->bind_param("ss", $hashed_password, $colaborador['correo']);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    
    // Obtener todos los departamentos
    public function obtenerDepartamentos() {
        $sql = "SELECT * FROM departamentos";
        $result = $this->conn->query($sql);
        $departamentos = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $departamentos[] = $row;
            }
        }
        return $departamentos;
    }

    // Obtener todas las categorías
    public function obtenerCategorias() {
        $sql = "SELECT * FROM categorias";
        $result = $this->conn->query($sql);
        $categorias = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categorias[] = $row;
            }
        }
        return $categorias;
    }

    // Obtener todo el inventario con nombre de categoría
    public function obtenerInventario() {
        $sql = "SELECT i.*, c.nombre AS categoria FROM inventario i 
                LEFT JOIN categorias c ON i.categoria_id = c.id";
        $result = $this->conn->query($sql);
        $inventario = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $inventario[] = $row;
            }
        }
        return $inventario;
    }

    // Obtener todas las solicitudes
    public function obtenerSolicitudes() {
        $sql = "SELECT s.*, i.nombre_equipo, c.nombre AS colaborador_nombre, c.apellido AS colaborador_apellido
                FROM solicitudes s
                LEFT JOIN inventario i ON s.inventario_id = i.id
                LEFT JOIN colaboradores c ON s.colaborador_id = c.id";
        $result = $this->conn->query($sql);
        $solicitudes = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $solicitudes[] = $row;
            }
        }
        return $solicitudes;
    }

    // Obtener todas las asignaciones
    public function obtenerAsignaciones() {
        $sql = "SELECT a.*, i.nombre_equipo, c.nombre AS colaborador_nombre, c.apellido AS colaborador_apellido
                FROM asignaciones a
                LEFT JOIN inventario i ON a.inventario_id = i.id
                LEFT JOIN colaboradores c ON a.colaborador_id = c.id";
        $result = $this->conn->query($sql);
        $asignaciones = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $asignaciones[] = $row;
            }
        }
        return $asignaciones;
    }

    // Obtener historial de accesos de un colaborador
    public function obtenerHistorialAccesosColaborador($colaborador_id) {
        $stmt = $this->conn->prepare("SELECT * FROM historial_accesos_colaborador WHERE colaborador_id = ?");
        $stmt->bind_param("i", $colaborador_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $historial = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $historial[] = $row;
            }
        }
        $stmt->close();
        return $historial;
    }

    public function obtenerInventarioDisponible() {
    $sql = "SELECT i.*, c.nombre as categoria FROM inventario i
            LEFT JOIN categorias c ON i.categoria_id = c.id
            WHERE i.estado IN ('activo', 'inventario')";
    $result = $this->getConexion()->query($sql);
    $inventario = [];
    while ($row = $result->fetch_assoc()) {
        $inventario[] = $row;
    }
    return $inventario;
    }

    // Métodos CRUD para Categorías
    
    // Insertar nueva categoría
    public function insertarCategoria($nombre, $descripcion) {
        $stmt = $this->conn->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $descripcion);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    // Actualizar categoría
    public function actualizarCategoria($id, $nombre, $descripcion) {
        $stmt = $this->conn->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nombre, $descripcion, $id);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    // Eliminar categoría
    public function eliminarCategoria($id) {
        // Verificar si la categoría está siendo usada en inventario
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM inventario WHERE categoria_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if ($row['count'] > 0) {
            return false; // No se puede eliminar porque está en uso
        }
        
        $stmt = $this->conn->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    // Obtener categoría por ID
    public function obtenerCategoriaPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $categoria = $result->fetch_assoc();
        $stmt->close();
        return $categoria;
    }

    // Verificar si el usuario es administrador
    public function esAdministrador($usuario_id) {
        $stmt = $this->conn->prepare("SELECT rol FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $stmt->close();
        
        return $usuario && $usuario['rol'] === 'admin';
    }

    // MÉTODOS PARA REPORTES

    // Obtener estadísticas por categoría
    public function obtenerEstadisticasPorCategoria() {
        $sql = "SELECT 
                    c.id as categoria_id,
                    c.nombre as categoria,
                    COUNT(i.id) as total_equipos,
                    COUNT(a.id) as equipos_asignados
                FROM categorias c
                LEFT JOIN inventario i ON c.id = i.categoria_id
                LEFT JOIN asignaciones a ON i.id = a.inventario_id AND a.estado = 'asignado'
                GROUP BY c.id, c.nombre
                ORDER BY c.nombre";
        
        $result = $this->conn->query($sql);
        $estadisticas = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $estadisticas[] = $row;
            }
        }
        return $estadisticas;
    }

    // Obtener equipos disponibles por categoría
    public function obtenerEquiposDisponiblesPorCategoria() {
        $sql = "SELECT 
                    c.nombre as categoria,
                    COUNT(i.id) as equipos_disponibles
                FROM categorias c
                LEFT JOIN inventario i ON c.id = i.categoria_id
                LEFT JOIN asignaciones a ON i.id = a.inventario_id AND a.estado = 'asignado'
                WHERE a.id IS NULL
                GROUP BY c.id, c.nombre
                ORDER BY c.nombre";
        
        $result = $this->conn->query($sql);
        $disponibles = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $disponibles[] = $row;
            }
        }
        return $disponibles;
    }

    // Obtener equipos asignados por categoría
    public function obtenerEquiposAsignadosPorCategoria() {
        $sql = "SELECT 
                    c.nombre as categoria,
                    COUNT(a.id) as equipos_asignados,
                    GROUP_CONCAT(CONCAT(col.nombre, ' ', col.apellido) SEPARATOR ', ') as colaboradores
                FROM categorias c
                LEFT JOIN inventario i ON c.id = i.categoria_id
                LEFT JOIN asignaciones a ON i.id = a.inventario_id AND a.estado = 'asignado'
                LEFT JOIN colaboradores col ON a.colaborador_id = col.id
                WHERE a.id IS NOT NULL
                GROUP BY c.id, c.nombre
                ORDER BY c.nombre";
        
        $result = $this->conn->query($sql);
        $asignados = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $asignados[] = $row;
            }
        }
        return $asignados;
    }

    // Obtener detalle de equipos por categoría
    public function obtenerEquiposPorCategoria($categoria_id, $estado = null) {
        $sql = "SELECT 
                    i.*,
                    c.nombre as categoria,
                    CASE 
                        WHEN a.id IS NOT NULL THEN 'Asignado'
                        ELSE 'Disponible'
                    END as estado_equipo,
                    CONCAT(col.nombre, ' ', col.apellido) as asignado_a,
                    a.fecha_asignacion
                FROM inventario i
                LEFT JOIN categorias c ON i.categoria_id = c.id
                LEFT JOIN asignaciones a ON i.id = a.inventario_id AND a.estado = 'asignado'
                LEFT JOIN colaboradores col ON a.colaborador_id = col.id
                WHERE i.categoria_id = ?";
        
        if ($estado === 'disponible') {
            $sql .= " AND a.id IS NULL";
        } elseif ($estado === 'asignado') {
            $sql .= " AND a.id IS NOT NULL";
        }
        
        $sql .= " ORDER BY i.nombre_equipo";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $categoria_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $equipos = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $equipos[] = $row;
            }
        }
        $stmt->close();
        return $equipos;
    }

    // Obtener reporte filtrado para exportación
    public function obtenerReporteFiltrado($categoria_id = null, $estado = null, $fecha_desde = null, $fecha_hasta = null) {
        $sql = "SELECT 
                    i.id,
                    i.nombre_equipo,
                    i.marca,
                    i.modelo,
                    i.numero_serie,
                    c.nombre as categoria,
                    CASE 
                        WHEN a.id IS NOT NULL THEN 'Asignado'
                        ELSE 'Disponible'
                    END as estado_equipo,
                    CONCAT(col.nombre, ' ', col.apellido) as asignado_a,
                    a.fecha_asignacion,
                    i.fecha_ingreso
                FROM inventario i
                LEFT JOIN categorias c ON i.categoria_id = c.id
                LEFT JOIN asignaciones a ON i.id = a.inventario_id AND a.estado = 'asignado'
                LEFT JOIN colaboradores col ON a.colaborador_id = col.id
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if ($categoria_id) {
            $sql .= " AND i.categoria_id = ?";
            $params[] = $categoria_id;
            $types .= "i";
        }
        
        if ($estado === 'disponible') {
            $sql .= " AND a.id IS NULL";
        } elseif ($estado === 'asignado') {
            $sql .= " AND a.id IS NOT NULL";
        }
        
        if ($fecha_desde) {
            $sql .= " AND DATE(i.fecha_ingreso) >= ?";
            $params[] = $fecha_desde;
            $types .= "s";
        }
        
        if ($fecha_hasta) {
            $sql .= " AND DATE(i.fecha_ingreso) <= ?";
            $params[] = $fecha_hasta;
            $types .= "s";
        }
        
        $sql .= " ORDER BY c.nombre, i.nombre_equipo";
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reporte = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $reporte[] = $row;
            }
        }
        $stmt->close();
        return $reporte;
    }

    // ===== MÉTODOS DE DESCARTE =====
    
    public function marcarDescarte($inventario_id, $observaciones, $tecnico) {
        try {
            // Primero verificar que el equipo exista y no esté ya en descarte
            $check_sql = "SELECT id, nombre_equipo, estado_descarte FROM inventario WHERE id = ?";
            $check_stmt = $this->conn->prepare($check_sql);
            $check_stmt->bind_param("i", $inventario_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows === 0) {
                return ['success' => false, 'message' => 'El equipo no existe'];
            }
            
            $equipo = $result->fetch_assoc();
            if ($equipo['estado_descarte'] === 'descarte') {
                return ['success' => false, 'message' => 'El equipo ya está marcado como descarte'];
            }
            
            $check_stmt->close();
            
            // Iniciar transacción
            $this->conn->begin_transaction();
            
            // Si el equipo está asignado, liberarlo primero
            $liberar_sql = "UPDATE asignaciones SET estado = 'devuelto', fecha_retiro = NOW() 
                           WHERE inventario_id = ? AND estado = 'asignado'";
            $liberar_stmt = $this->conn->prepare($liberar_sql);
            $liberar_stmt->bind_param("i", $inventario_id);
            $liberar_stmt->execute();
            $liberar_stmt->close();
            
            // Marcar equipo como descarte
            $descarte_sql = "UPDATE inventario SET 
                            estado_descarte = 'descarte',
                            fecha_descarte = NOW(),
                            observaciones_descarte = ?,
                            tecnico_descarte = ?
                            WHERE id = ?";
            
            $descarte_stmt = $this->conn->prepare($descarte_sql);
            $descarte_stmt->bind_param("ssi", $observaciones, $tecnico, $inventario_id);
            
            if ($descarte_stmt->execute()) {
                $this->conn->commit();
                $descarte_stmt->close();
                return ['success' => true, 'message' => 'Equipo marcado como descarte correctamente'];
            } else {
                $this->conn->rollback();
                $descarte_stmt->close();
                return ['success' => false, 'message' => 'Error al marcar el equipo como descarte'];
            }
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    public function restaurarDescarte($inventario_id) {
        try {
            $sql = "UPDATE inventario SET 
                    estado_descarte = 'activo',
                    fecha_descarte = NULL,
                    observaciones_descarte = NULL,
                    tecnico_descarte = NULL
                    WHERE id = ? AND estado_descarte = 'descarte'";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $inventario_id);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $stmt->close();
                return ['success' => true, 'message' => 'Equipo restaurado del descarte correctamente'];
            } else {
                $stmt->close();
                return ['success' => false, 'message' => 'No se pudo restaurar el equipo o no estaba en descarte'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    public function obtenerEquiposDescarte() {
        $sql = "SELECT i.*, c.nombre as categoria 
                FROM inventario i
                LEFT JOIN categorias c ON i.categoria_id = c.id
                WHERE i.estado_descarte = 'descarte'
                ORDER BY i.fecha_descarte DESC";
        
        $result = $this->conn->query($sql);
        $equipos = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $equipos[] = $row;
            }
        }
        
        return $equipos;
    }
    
    public function obtenerDetalleDescarte($inventario_id) {
        $sql = "SELECT i.*, c.nombre as categoria 
                FROM inventario i
                LEFT JOIN categorias c ON i.categoria_id = c.id
                WHERE i.id = ? AND i.estado_descarte = 'descarte'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $inventario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $detalle = null;
        if ($result && $result->num_rows > 0) {
            $detalle = $result->fetch_assoc();
        }
        
        $stmt->close();
        return $detalle;
    }
    
    // Obtener equipos asignados a un colaborador específico
    public function obtenerEquiposAsignadosColaborador($colaborador_id) {
        $sql = "SELECT a.id as asignacion_id, a.fecha_asignacion, a.estado as estado_asignacion,
                       i.id as inventario_id, i.nombre_equipo, i.marca, i.modelo, i.numero_serie, 
                       i.imagen, i.costo, c.nombre as categoria
                FROM asignaciones a
                INNER JOIN inventario i ON a.inventario_id = i.id
                LEFT JOIN categorias c ON i.categoria_id = c.id
                WHERE a.colaborador_id = ? AND a.estado = 'asignado'
                ORDER BY a.fecha_asignacion DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $colaborador_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $equipos = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $equipos[] = $row;
            }
        }
        
        $stmt->close();
        return $equipos;
    }
    
    // Procesar entrega de equipo por colaborador
    // MÉTODO COMENTADO - Tabla entregas_colaborador eliminada
    /*
    public function procesarEntregaEquipo($asignacion_id, $colaborador_id, $motivo_entrega, $tipo_entrega, $observaciones) {
        try {
            $this->conn->begin_transaction();
            
            // Verificar que la asignación existe y pertenece al colaborador
            $stmt = $this->conn->prepare("
                SELECT a.id, a.inventario_id, i.nombre_equipo, c.nombre as colaborador_nombre, c.apellido
                FROM asignaciones a
                INNER JOIN inventario i ON a.inventario_id = i.id
                INNER JOIN colaboradores c ON a.colaborador_id = c.id
                WHERE a.id = ? AND a.colaborador_id = ? AND a.estado = 'asignado'
            ");
            $stmt->bind_param("ii", $asignacion_id, $colaborador_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("No se encontró la asignación o el equipo ya fue entregado");
            }
            
            $asignacion = $result->fetch_assoc();
            $stmt->close();
            
            // Crear registro en tabla de entregas
            $stmt = $this->conn->prepare("
                INSERT INTO entregas_colaborador 
                (asignacion_id, colaborador_id, inventario_id, motivo_entrega, tipo_entrega, observaciones, fecha_entrega, estado) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), 'pendiente_validacion')
            ");
            $stmt->bind_param("iiisss", $asignacion_id, $colaborador_id, $asignacion['inventario_id'], 
                             $motivo_entrega, $tipo_entrega, $observaciones);
            $stmt->execute();
            $stmt->close();
            
            // Actualizar estado de la asignación
            $stmt = $this->conn->prepare("
                UPDATE asignaciones 
                SET estado = 'entrega_pendiente', fecha_retiro = NOW(), motivo_retiro = ?
                WHERE id = ?
            ");
            $motivo_completo = "Entrega por colaborador - " . $tipo_entrega . ": " . $motivo_entrega;
            $stmt->bind_param("si", $motivo_completo, $asignacion_id);
            $stmt->execute();
            $stmt->close();
            
            // Actualizar estado del inventario
            $stmt = $this->conn->prepare("
                UPDATE inventario 
                SET estado = 'entrega_pendiente'
                WHERE id = ?
            ");
            $stmt->bind_param("i", $asignacion['inventario_id']);
            $stmt->execute();
            $stmt->close();
            
            $this->conn->commit();
            
            return [
                'success' => true, 
                'message' => 'Entrega procesada correctamente. El equipo está pendiente de validación por un administrador.'
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                'success' => false, 
                'message' => 'Error al procesar la entrega: ' . $e->getMessage()
            ];
        }
    }
    */
    
    // MÉTODO COMENTADO - Tabla entregas_colaborador eliminada
    /*
    // Obtener entregas pendientes de validación
    public function obtenerEntregasPendientes() {
        $sql = "SELECT e.*, 
                       c.nombre as colaborador_nombre, c.apellido as colaborador_apellido, c.foto as colaborador_foto,
                       i.nombre_equipo, i.marca, i.modelo, i.numero_serie, i.imagen as equipo_imagen,
                       cat.nombre as categoria
                FROM entregas_colaborador e
                INNER JOIN colaboradores c ON e.colaborador_id = c.id
                INNER JOIN inventario i ON e.inventario_id = i.id
                LEFT JOIN categorias cat ON i.categoria_id = cat.id
                WHERE e.estado = 'pendiente_validacion'
                ORDER BY e.fecha_entrega DESC";
        
        $result = $this->conn->query($sql);
        $entregas = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $entregas[] = $row;
            }
        }
        
        return $entregas;
    }
    */
    
    // MÉTODO COMENTADO - Tabla entregas_colaborador eliminada
    /*
    // Validar entrega de equipo por administrador
    public function validarEntregaEquipo($entrega_id, $usuario_admin_id, $accion, $observaciones_admin = '') {
        try {
            $this->conn->begin_transaction();
            
            // Obtener datos de la entrega
            $stmt = $this->conn->prepare("
                SELECT e.*, i.nombre_equipo
                FROM entregas_colaborador e
                INNER JOIN inventario i ON e.inventario_id = i.id
                WHERE e.id = ? AND e.estado = 'pendiente_validacion'
            ");
            $stmt->bind_param("i", $entrega_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Entrega no encontrada o ya fue procesada");
            }
            
            $entrega = $result->fetch_assoc();
            $stmt->close();
            
            if ($accion === 'aprobar') {
                // Aprobar entrega - equipo pasa a revisión técnica
                $stmt = $this->conn->prepare("
                    UPDATE entregas_colaborador 
                    SET estado = 'aprobada', usuario_admin_id = ?, fecha_validacion = NOW(), observaciones_admin = ?
                    WHERE id = ?
                ");
                $stmt->bind_param("isi", $usuario_admin_id, $observaciones_admin, $entrega_id);
                $stmt->execute();
                $stmt->close();
                
                // Actualizar asignación
                $stmt = $this->conn->prepare("
                    UPDATE asignaciones 
                    SET estado = 'devuelto'
                    WHERE id = ?
                ");
                $stmt->bind_param("i", $entrega['asignacion_id']);
                $stmt->execute();
                $stmt->close();
                
                // Actualizar inventario a revisión técnica
                $stmt = $this->conn->prepare("
                    UPDATE inventario 
                    SET estado = 'revision_tecnica'
                    WHERE id = ?
                ");
                $stmt->bind_param("i", $entrega['inventario_id']);
                $stmt->execute();
                $stmt->close();
                
                $mensaje = 'Entrega aprobada. El equipo está ahora en revisión técnica.';
                
            } else {
                // Rechazar entrega - equipo vuelve al colaborador
                $stmt = $this->conn->prepare("
                    UPDATE entregas_colaborador 
                    SET estado = 'rechazada', usuario_admin_id = ?, fecha_validacion = NOW(), observaciones_admin = ?
                    WHERE id = ?
                ");
                $stmt->bind_param("isi", $usuario_admin_id, $observaciones_admin, $entrega_id);
                $stmt->execute();
                $stmt->close();
                
                // Restaurar asignación
                $stmt = $this->conn->prepare("
                    UPDATE asignaciones 
                    SET estado = 'asignado', fecha_retiro = NULL, motivo_retiro = NULL
                    WHERE id = ?
                ");
                $stmt->bind_param("i", $entrega['asignacion_id']);
                $stmt->execute();
                $stmt->close();
                
                // Restaurar inventario
                $stmt = $this->conn->prepare("
                    UPDATE inventario 
                    SET estado = 'asignado'
                    WHERE id = ?
                ");
                $stmt->bind_param("i", $entrega['inventario_id']);
                $stmt->execute();
                $stmt->close();
                
                $mensaje = 'Entrega rechazada. El equipo vuelve a estar asignado al colaborador.';
            }
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'message' => $mensaje
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                'success' => false,
                'message' => 'Error al validar la entrega: ' . $e->getMessage()
            ];
        }
    }
    */
    
    // Procesar donación de equipo
    public function procesarSolicitudDonacion($inventario_id, $colaborador_id, $destinatario, $motivo) {
        try {
            $this->conn->begin_transaction();
            
            // Verificar que el equipo esté asignado al colaborador
            $stmt = $this->conn->prepare("
                SELECT a.id as asignacion_id, i.nombre_equipo
                FROM asignaciones a
                INNER JOIN inventario i ON a.inventario_id = i.id
                WHERE a.inventario_id = ? AND a.colaborador_id = ? AND a.estado = 'asignado'
            ");
            $stmt->bind_param("ii", $inventario_id, $colaborador_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("El equipo no está asignado a este colaborador");
            }
            
            $asignacion = $result->fetch_assoc();
            $stmt->close();
            
            // Insertar solicitud de donación
            $stmt = $this->conn->prepare("
                INSERT INTO donaciones 
                (colaborador_id, inventario_id, destinatario, motivo, fecha_donacion, estado) 
                VALUES (?, ?, ?, ?, NOW(), 'pendiente')
            ");
            $stmt->bind_param("iiss", $colaborador_id, $inventario_id, $destinatario, $motivo);
            $stmt->execute();
            $stmt->close();
            
            // Marcar equipo como pendiente de donación
            $stmt = $this->conn->prepare("
                UPDATE inventario 
                SET estado = 'donacion_pendiente'
                WHERE id = ?
            ");
            $stmt->bind_param("i", $inventario_id);
            $stmt->execute();
            $stmt->close();
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'message' => 'Solicitud de donación enviada correctamente. Está pendiente de aprobación.'
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ];
        }
    }
    
    // Obtener solicitudes de donación pendientes
    public function obtenerSolicitudesDonacion() {
        $sql = "SELECT d.*, 
                       c.id as colaborador_id, c.nombre as colaborador_nombre, c.apellido as colaborador_apellido, c.foto as colaborador_foto,
                       i.nombre_equipo, i.marca, i.modelo, i.numero_serie, i.imagen as equipo_imagen,
                       cat.nombre as categoria
                FROM donaciones d
                INNER JOIN colaboradores c ON d.colaborador_id = c.id
                INNER JOIN inventario i ON d.inventario_id = i.id
                LEFT JOIN categorias cat ON i.categoria_id = cat.id
                WHERE d.estado = 'pendiente'
                ORDER BY d.fecha_donacion DESC";
        
        $result = $this->conn->query($sql);
        $donaciones = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $donaciones[] = $row;
            }
        }
        
        return $donaciones;
    }
    
    // Procesar donación por administrador
    public function procesarDonacion($donacion_id, $usuario_admin_id, $accion) {
        try {
            $this->conn->begin_transaction();
            
            // Obtener datos de la donación
            $stmt = $this->conn->prepare("
                SELECT d.*, i.nombre_equipo, a.id as asignacion_id
                FROM donaciones d
                INNER JOIN inventario i ON d.inventario_id = i.id
                LEFT JOIN asignaciones a ON (a.inventario_id = d.inventario_id AND a.colaborador_id = d.colaborador_id AND a.estado = 'asignado')
                WHERE d.id = ? AND d.estado = 'pendiente'
            ");
            $stmt->bind_param("i", $donacion_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Donación no encontrada o ya fue procesada");
            }
            
            $donacion = $result->fetch_assoc();
            $stmt->close();
            
            if ($accion === 'aprobar') {
                // Aprobar donación - equipo sale del inventario
                $stmt = $this->conn->prepare("
                    UPDATE donaciones 
                    SET estado = 'aprobada', usuario_admin_id = ?, fecha_respuesta = NOW()
                    WHERE id = ?
                ");
                $stmt->bind_param("ii", $usuario_admin_id, $donacion_id);
                $stmt->execute();
                $stmt->close();
                
                // Actualizar asignación si existe
                if ($donacion['asignacion_id']) {
                    $stmt = $this->conn->prepare("
                        UPDATE asignaciones 
                        SET estado = 'donado', fecha_retiro = NOW(), motivo_retiro = 'Equipo donado'
                        WHERE id = ?
                    ");
                    $stmt->bind_param("i", $donacion['asignacion_id']);
                    $stmt->execute();
                    $stmt->close();
                }
                
                // Actualizar inventario - equipo donado sale del inventario
                $stmt = $this->conn->prepare("
                    UPDATE inventario 
                    SET estado = 'donado'
                    WHERE id = ?
                ");
                $stmt->bind_param("i", $donacion['inventario_id']);
                $stmt->execute();
                $stmt->close();
                
                $mensaje = 'Donación aprobada. El equipo ha sido donado y sale del inventario.';
                
            } else {
                // Rechazar donación
                $stmt = $this->conn->prepare("
                    UPDATE donaciones 
                    SET estado = 'rechazada', usuario_admin_id = ?, fecha_respuesta = NOW()
                    WHERE id = ?
                ");
                $stmt->bind_param("ii", $usuario_admin_id, $donacion_id);
                $stmt->execute();
                $stmt->close();
                
                // Restaurar estado del inventario
                $stmt = $this->conn->prepare("
                    UPDATE inventario 
                    SET estado = 'asignado'
                    WHERE id = ?
                ");
                $stmt->bind_param("i", $donacion['inventario_id']);
                $stmt->execute();
                $stmt->close();
                
                $mensaje = 'Donación rechazada. El equipo continúa asignado.';
            }
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'message' => $mensaje
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                'success' => false,
                'message' => 'Error al procesar la donación: ' . $e->getMessage()
            ];
        }
    }

    // Obtener todos los usuarios
    public function obtenerUsuarios() {
        try {
            $sql = "SELECT id, nombre, correo, rol, activo, foto, fecha_creacion FROM usuarios ORDER BY id ASC";
            $result = $this->conn->query($sql);
            
            if ($result) {
                return $result->fetch_all(MYSQLI_ASSOC);
            } else {
                return [];
            }
        } catch (Exception $e) {
            error_log("Error en obtenerUsuarios(): " . $e->getMessage());
            return [];
        }
    }

    // Métodos para gestión de colaboradores
    public function existeIdentificacionColaborador($identificacion) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM colaboradores WHERE identificacion = ? AND activo = 1");
            $stmt->bind_param("s", $identificacion);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row['count'] > 0;
        } catch (Exception $e) {
            error_log("Error en existeIdentificacionColaborador(): " . $e->getMessage());
            return false;
        }
    }

    public function existeUsuarioColaborador($usuario) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM usuarios WHERE nombre = ? AND rol = 'colab' AND activo = 1");
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row['count'] > 0;
        } catch (Exception $e) {
            error_log("Error en existeUsuarioColaborador(): " . $e->getMessage());
            return false;
        }
    }

    public function existeCorreoColaborador($correo) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM colaboradores WHERE correo = ? AND activo = 1");
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row['count'] > 0;
        } catch (Exception $e) {
            error_log("Error en existeCorreoColaborador(): " . $e->getMessage());
            return false;
        }
    }

    public function insertarColaborador($nombre, $apellido, $identificacion, $foto, $direccion, $ubicacion, $telefono, $correo, $departamento_id, $usuario, $contrasena) {
        try {
            $this->conn->begin_transaction();

            // 1. Insertar en tabla colaboradores
            $stmt = $this->conn->prepare("
                INSERT INTO colaboradores (nombre, apellido, identificacion, foto, direccion, ubicacion, telefono, correo, departamento_id, activo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->bind_param("ssssssssi", $nombre, $apellido, $identificacion, $foto, $direccion, $ubicacion, $telefono, $correo, $departamento_id);
            $stmt->execute();
            $colaborador_id = $this->conn->insert_id;
            $stmt->close();

            // 2. Insertar en tabla usuarios
            $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("
                INSERT INTO usuarios (nombre, correo, contrasena, rol, activo, created_at) 
                VALUES (?, ?, ?, 'colab', 1, NOW())
            ");
            $stmt->bind_param("sss", $usuario, $correo, $contrasena_hash);
            $stmt->execute();
            $stmt->close();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error en insertarColaborador(): " . $e->getMessage());
            return false;
        }
    }

    // Métodos para CRUD de Usuarios
    public function verificarUsuarioExiste($correo) {
        $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    public function verificarCorreoDuplicado($correo, $excluir_id) {
        $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE correo = ? AND id != ?");
        $stmt->bind_param("si", $correo, $excluir_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    public function crearUsuario($nombre, $correo, $rol, $contrasena, $activo) {
        try {
            $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("INSERT INTO usuarios (nombre, correo, rol, contrasena, activo, fecha_creacion) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssssi", $nombre, $correo, $rol, $contrasena_hash, $activo);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        } catch (Exception $e) {
            error_log("Error en crearUsuario(): " . $e->getMessage());
            return false;
        }
    }

    public function actualizarUsuario($id, $nombre, $correo, $rol, $activo, $contrasena = null) {
        try {
            if ($contrasena) {
                // Actualizar con nueva contraseña
                $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                $stmt = $this->conn->prepare("UPDATE usuarios SET nombre = ?, correo = ?, rol = ?, contrasena = ?, activo = ? WHERE id = ?");
                $stmt->bind_param("ssssii", $nombre, $correo, $rol, $contrasena_hash, $activo, $id);
            } else {
                // Actualizar sin cambiar contraseña
                $stmt = $this->conn->prepare("UPDATE usuarios SET nombre = ?, correo = ?, rol = ?, activo = ? WHERE id = ?");
                $stmt->bind_param("sssii", $nombre, $correo, $rol, $activo, $id);
            }
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        } catch (Exception $e) {
            error_log("Error en actualizarUsuario(): " . $e->getMessage());
            return false;
        }
    }
}
?>