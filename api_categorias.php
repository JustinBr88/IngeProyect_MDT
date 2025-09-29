<?php
require_once 'conexion.php';
header('Content-Type: application/json');

if (isset($_GET['action']) && $_GET['action'] === 'getCategorias') {
    $conexion = new Conexion();
    $categorias = $conexion->obtenerCategorias();
    echo json_encode($categorias);
    exit;
}

// Si no hay acción válida, devolver error
echo json_encode(['error' => 'Acción no válida']);
?>
