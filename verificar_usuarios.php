<?php
require_once 'conexion.php';

echo "<h2>🔍 Verificación de tabla usuarios</h2>";

try {
    $conexion = new Conexion();
    $mysqli = $conexion->getConexion();
    
    // Mostrar estructura de la tabla
    echo "<h3>Estructura de la tabla usuarios:</h3>";
    $result = $mysqli->query("DESCRIBE usuarios");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Mostrar usuarios existentes
    echo "<h3>Usuarios existentes:</h3>";
    $result = $mysqli->query("SELECT id, nombre, correo, rol, activo, fecha_creacion FROM usuarios");
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Activo</th><th>Fecha Creación</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['nombre']}</td>";
            echo "<td>{$row['correo']}</td>";
            echo "<td>{$row['rol']}</td>";
            echo "<td>{$row['activo']}</td>";
            echo "<td>{$row['fecha_creacion']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hay usuarios registrados.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
