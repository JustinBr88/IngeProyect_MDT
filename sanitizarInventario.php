<?php
/**
 * ARCHIVO DE SANITIZACIÓN PARA EL SISTEMA DE INVENTARIO
 * Este archivo contiene las versiones sanitizadas de todos los archivos principales
 * para prevenir vulnerabilidades XSS y SQL Injection
 */

// =====================================================
// 1. ASIGNACIONES.PHP SANITIZADO
// =====================================================
?>
<!-- INICIO: Asignaciones.php SANITIZADO -->
<?php 
include('navbar.php'); 
include('../conexion.php');
$conexion = new Conexion();

// SANITIZADO: Usar prepared statement en lugar de query directo
$stmt = $conexion->getConexion()->prepare("
    SELECT a.id, a.inventario_id, a.colaborador_id, a.fecha_asignacion, a.fecha_retiro, a.estado as estado_asignacion,
           c.id as colaborador_id, c.id as colaborador_id, c.nombre as colaborador_nombre, c.foto as colaborador_foto,
           i.nombre_equipo, i.imagen as equipo_imagen, i.marca, i.modelo, i.numero_serie, i.costo, i.estado as estado_equipo,
           (SELECT estado FROM donaciones WHERE inventario_id = a.inventario_id AND estado = 'aprobada' LIMIT 1) as fue_donado
    FROM asignaciones a
    LEFT JOIN colaboradores c ON a.colaborador_id = c.id
    LEFT JOIN inventario i ON a.inventario_id = i.id
    WHERE a.estado = 'asignado'
    ORDER BY a.fecha_asignacion DESC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-5">
    <h2>Asignaciones de equipos</h2>
    <div class="d-flex mb-3 justify-content-end">
        <a href="Inventario.php" class="btn btn-primary">Volver a Inventario</a>
    </div>
    <table class="table table-bordered table-striped mt-4" id="tabla-asignaciones-admin">
        <thead class="thead-dark">
            <tr>
                <th>Colaborador</th>
                <th>Equipo</th>
                <th>Imagen</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Serie</th>
                <th>Costo</th>
                <th>Estado equipo</th>
                <th>Fecha Asignación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $result->fetch_assoc()) {
                // SANITIZADO: Escapar rutas de imágenes
                $foto = "../mostrar_foto_usuario.php?tipo=colaborador&id=" . htmlspecialchars($row['colaborador_id']);
                $imgEquipo = !empty($row['equipo_imagen']) ? "../uploads/" . htmlspecialchars($row['equipo_imagen']) : "../img/equipo.jpg";
                
                // SANITIZADO: Escapar todos los data attributes
                echo "<tr data-id='" . htmlspecialchars($row['id']) . "' 
                          data-inventario='" . htmlspecialchars($row['inventario_id']) . "'
                          data-colaborador='" . htmlspecialchars($row['colaborador_id']) . "'
                          data-nombre='" . htmlspecialchars($row['nombre_equipo']) . "'
                          data-marca='" . htmlspecialchars($row['marca']) . "'
                          data-modelo='" . htmlspecialchars($row['modelo']) . "'
                          data-serie='" . htmlspecialchars($row['numero_serie']) . "'
                          data-costo='" . htmlspecialchars($row['costo']) . "'
                          data-estado='" . htmlspecialchars($row['estado_equipo']) . "'
                          data-fecha-asignacion='" . htmlspecialchars($row['fecha_asignacion']) . "'
                          data-donado='" . htmlspecialchars($row['fue_donado'] ?? '') . "'>";
                
                // SANITIZADO: Escapar todo el contenido de las celdas
                echo "<td class='text-center'>
                        <img src='" . htmlspecialchars($foto) . "' class='rounded-circle mb-1' width='48' height='48' alt='Foto colaborador'><br>
                        <span>" . htmlspecialchars($row['colaborador_nombre']) . "</span>
                     </td>
                     <td>" . htmlspecialchars($row['nombre_equipo']) . "</td>
                     <td><img src='" . htmlspecialchars($imgEquipo) . "' width='60' alt='Imagen equipo'></td>
                     <td>" . htmlspecialchars($row['marca']) . "</td>
                     <td>" . htmlspecialchars($row['modelo']) . "</td>
                     <td>" . htmlspecialchars($row['numero_serie']) . "</td>
                     <td>" . htmlspecialchars($row['costo']) . "</td>
                     <td>" . htmlspecialchars($row['estado_equipo']) . "</td>
                     <td>" . htmlspecialchars($row['fecha_asignacion']) . "</td>
                     <td>";
                
                if ($row['fue_donado'] === 'aprobada') {
                    echo "<span class='text-success'>Equipo donado</span>";
                } else {
                    echo "<button class='btn btn-danger btn-sm btn-retirar' type='button'>Retirar</button>";
                }
                echo "</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<!-- FIN: Asignaciones.php SANITIZADO -->

<?php
// =====================================================
// 2. SOLICITUDES.PHP SANITIZADO
// =====================================================
?>
<!-- INICIO: Solicitudes.php SANITIZADO -->
<?php 
require_once '../vendor/autoload.php';
include('navbar.php'); 
include('../conexion.php');
$conexion = new Conexion();

// SANITIZADO: Usar prepared statement
$stmt = $conexion->getConexion()->prepare("
    SELECT s.id, s.inventario_id, s.nombre_equipo, s.fecha_solicitud, s.estado, s.motivo,
           c.id as colaborador_id, c.nombre as colaborador_nombre, c.foto as colaborador_foto,
           i.imagen as equipo_imagen, i.categoria_id, i.marca, i.modelo, i.numero_serie, i.costo, i.fecha_ingreso, i.tiempo_depreciacion,
           cat.nombre as categoria_nombre, i.estado as equipo_estado
    FROM solicitudes s
    LEFT JOIN colaboradores c ON s.colaborador_id = c.id
    LEFT JOIN inventario i ON s.inventario_id = i.id
    LEFT JOIN categorias cat ON i.categoria_id = cat.id
    WHERE s.estado = 'pendiente'
    ORDER BY s.fecha_solicitud DESC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-5">
    <h2>Solicitudes de equipos - Administración</h2>
    <div class="d-flex mb-3 justify-content-end">
        <a href="Inventario.php" class="btn btn-primary me-2">Volver a Inventario</a>
        <a href="Asignaciones.php" class="btn btn-info">Ver Asignaciones</a>
    </div>
    <table class="table table-bordered table-striped mt-4" id="tabla-solicitudes-admin">
        <thead class="thead-dark">
            <tr>
                <th>Colaborador</th>
                <th>Equipo</th>
                <th>Imagen</th>
                <th>Categoría</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Serie</th>
                <th>Costo</th>
                <th>Ingreso</th>
                <th>Depreciación</th>
                <th>Motivo</th>
                <th>QR</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $result->fetch_assoc()) {
                // SANITIZADO: Escapar rutas de imágenes
                $foto = "../mostrar_foto_usuario.php?tipo=colaborador&id=" . htmlspecialchars($row['colaborador_id']);
                $imgEquipo = !empty($row['equipo_imagen']) ? "../uploads/" . htmlspecialchars($row['equipo_imagen']) : "../img/equipo.jpg";
                
                // SANITIZADO: Escapar todos los data attributes
                echo "<tr data-id='" . htmlspecialchars($row['id']) . "' 
                          data-inventario='" . htmlspecialchars($row['inventario_id']) . "'
                          data-nombre='" . htmlspecialchars($row['nombre_equipo']) . "'
                          data-categoria='" . htmlspecialchars($row['categoria_nombre'] ?? '') . "'
                          data-marca='" . htmlspecialchars($row['marca']) . "'
                          data-modelo='" . htmlspecialchars($row['modelo']) . "'
                          data-serie='" . htmlspecialchars($row['numero_serie']) . "'
                          data-costo='" . htmlspecialchars($row['costo']) . "'
                          data-ingreso='" . htmlspecialchars($row['fecha_ingreso']) . "'
                          data-depreciacion='" . htmlspecialchars($row['tiempo_depreciacion']) . "'>";
                
                // SANITIZADO: Escapar todo el contenido de las celdas
                echo "<td class='text-center'>
                        <img src='" . htmlspecialchars($foto) . "' class='rounded-circle mb-1' width='48' height='48' alt='Foto colaborador'><br>
                        <span>" . htmlspecialchars($row['colaborador_nombre']) . "</span>
                     </td>
                     <td>" . htmlspecialchars($row['nombre_equipo']) . "</td>
                     <td><img src='" . htmlspecialchars($imgEquipo) . "' width='60' alt='Imagen equipo'></td>
                     <td>" . htmlspecialchars($row['categoria_nombre'] ?? 'N/A') . "</td>
                     <td>" . htmlspecialchars($row['marca']) . "</td>
                     <td>" . htmlspecialchars($row['modelo']) . "</td>
                     <td>" . htmlspecialchars($row['numero_serie']) . "</td>
                     <td>" . htmlspecialchars($row['costo']) . "</td>
                     <td>" . htmlspecialchars($row['fecha_ingreso']) . "</td>
                     <td>" . htmlspecialchars($row['tiempo_depreciacion']) . "</td>
                     <td><small>" . htmlspecialchars(substr($row['motivo'], 0, 50)) . "...</small></td>
                     <td>
                        <button class='btn btn-success btn-sm btn-qr' type='button'>QR</button>
                     </td>
                     <td>
                        <button class='btn btn-success btn-sm btn-aprobar me-1' type='button'>Aprobar</button>
                        <button class='btn btn-danger btn-sm btn-rechazar' type='button'>Rechazar</button>
                     </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<!-- FIN: Solicitudes.php SANITIZADO -->

<?php
// =====================================================
// 3. INVENTARIO COLABORADORES SANITIZADO
// =====================================================
?>
<!-- INICIO: InventarioColab.php SANITIZADO -->
<?php 
require_once '../vendor/autoload.php';
include('navbar.php'); 
include('../conexion.php');
$conexion = new Conexion();

// SANITIZADO: Usar método de la clase en lugar de query directo
$result = $conexion->obtenerInventarioDisponible();
?>

<div class="container mt-5">
    <h2>Inventario disponible para solicitud</h2>
    <table class="table table-bordered table-striped mt-4" id="tabla-inventario-colab">
        <thead class="thead-dark">
            <tr>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Serie</th>
                <th>Costo</th>
                <th>Ingreso</th>
                <th>Depreciación</th>
                <th>Solicitar</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($result as $row) {
                // SANITIZADO: Escapar todos los data attributes
                echo "<tr data-id='" . htmlspecialchars($row['id']) . "' 
                          data-nombre='" . htmlspecialchars($row['nombre_equipo']) . "'>";
                
                echo "<td>";
                if (!empty($row['imagen'])) {
                    echo "<img src='../uploads/" . htmlspecialchars($row['imagen']) . "' width='60' alt='Imagen equipo'>";
                } else {
                    echo "<img src='../img/equipo.jpg' width='60' alt='Imagen por defecto'>";
                }
                echo "</td>";
                
                // SANITIZADO: Escapar todo el contenido de las celdas
                echo "<td>" . htmlspecialchars($row['nombre_equipo']) . "</td>
                      <td>" . htmlspecialchars($row['categoria']) . "</td>
                      <td>" . htmlspecialchars($row['marca']) . "</td>
                      <td>" . htmlspecialchars($row['modelo']) . "</td>
                      <td>" . htmlspecialchars($row['numero_serie']) . "</td>
                      <td>" . htmlspecialchars($row['costo']) . "</td>
                      <td>" . htmlspecialchars($row['fecha_ingreso']) . "</td>
                      <td>" . htmlspecialchars($row['tiempo_depreciacion']) . "</td>
                      <td>";
                
                if ($row['estado'] === "activo" || $row['estado'] === "inventario") {
                    echo "<button class='btn btn-primary btn-sm btn-solicitar' type='button'>Solicitar</button>
                          <button class='btn btn-success btn-sm btn-qr ms-1' type='button'>QR</button>";
                } else {
                    echo "<span class='text-muted'>No disponible</span>";
                }
                echo "</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<!-- FIN: InventarioColab.php SANITIZADO -->

<?php
// =====================================================
// 4. INVENTARIO ADMINISTRADOR SANITIZADO (PARCIAL)
// =====================================================
?>
<!-- INICIO: Inventario.php SANITIZADO -->
<!-- 
NOTA: Para Inventario.php se requiere sanitizar también:
- Los modales de edición con validación de archivos
- Las funciones JavaScript que manejan datos
- Los endpoints PHP que procesan las actualizaciones

Ejemplo de cómo sanitizar la tabla principal:
-->
<?php
include('navbar.php');
require_once '../conexion.php';
$conexion = new Conexion();
$result = $conexion->obtenerInventario();
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Inventario - Equipos y Software</h2>
        <a href="Solicitudes.php" class="btn btn-warning">Ver Solicitudes</a>
    </div>
    <table class="table table-bordered table-striped mt-4" id="tabla-inventario">
        <thead class="thead-dark">
            <tr>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Serie</th>
                <th>Costo</th>
                <th>Ingreso</th>
                <th>Depreciación</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($result as $row) {
                // SANITIZADO: Escapar todos los data attributes
                echo "<tr data-id='" . htmlspecialchars($row['id']) . "'>";
                
                echo "<td>";
                if (!empty($row['imagen'])) {
                    echo "<img src='../uploads/" . htmlspecialchars($row['imagen']) . "' width='60' alt='Imagen equipo'>";
                } else {
                    echo "<img src='../img/equipo.jpg' width='60' alt='Imagen por defecto'>";
                }
                echo "</td>";
                
                // SANITIZADO: Todas las celdas editables con escape
                echo "<td class='editable' data-campo='nombre_equipo'>" . htmlspecialchars($row['nombre_equipo']) . "</td>
                      <td class='editable' data-campo='categoria_id'>" . htmlspecialchars($row['categoria']) . "</td>
                      <td class='editable' data-campo='marca'>" . htmlspecialchars($row['marca']) . "</td>
                      <td class='editable' data-campo='modelo'>" . htmlspecialchars($row['modelo']) . "</td>
                      <td class='editable' data-campo='numero_serie'>" . htmlspecialchars($row['numero_serie']) . "</td>
                      <td class='editable' data-campo='costo'>" . htmlspecialchars($row['costo']) . "</td>
                      <td class='editable' data-campo='fecha_ingreso'>" . htmlspecialchars($row['fecha_ingreso']) . "</td>
                      <td class='editable' data-campo='tiempo_depreciacion'>" . htmlspecialchars($row['tiempo_depreciacion']) . "</td>
                      <td class='editable' data-campo='estado'>" . htmlspecialchars($row['estado']) . "</td>
                      <td>
                        <button class='btn btn-sm btn-warning btn-editar'>Editar</button>
                        <button class='btn btn-sm btn-success btn-guardar d-none'>Guardar</button>
                      </td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<!-- FIN: Inventario.php SANITIZADO -->

<?php
// =====================================================
// NOTAS IMPORTANTES PARA IMPLEMENTACIÓN COMPLETA:
// =====================================================
/*
1. ARCHIVOS PHP DE CONEXIÓN:
   - conexinvetcolab.php: Necesita validación de entrada y prepared statements
   - conexsolicitudes.php: Necesita sanitización de parámetros JSON
   - conexinventario.php: Requiere validación de uploads y datos

2. ARCHIVOS JAVASCRIPT:
   - Validar datos antes de enviar al servidor
   - Escapar contenido dinámico antes de insertarlo en el DOM
   - Validar respuestas del servidor

3. VALIDACIONES ADICIONALES:
   - Upload de archivos: Validar tipo, tamaño y contenido
   - Sesiones: Verificar permisos de usuario
   - CSRF: Implementar tokens para formularios

4. HEADERS DE SEGURIDAD:
   - Content-Security-Policy
   - X-XSS-Protection
   - X-Content-Type-Options

PARA IMPLEMENTAR:
1. Reemplazar el contenido original de cada archivo con su versión sanitizada
2. Actualizar las funciones de conexión para usar prepared statements
3. Validar todos los archivos JavaScript
4. Implementar validaciones de upload seguras
*/
?>