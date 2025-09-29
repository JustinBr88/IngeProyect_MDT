<?php
session_start();
if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header('Location: Login.php');
    exit;
}

// Limpiar variables de colaborador si existen (para evitar conflictos)
if (isset($_SESSION['colaborador_logeado'])) {
    unset($_SESSION['colaborador_logeado']);
    unset($_SESSION['colaborador_id']);
    unset($_SESSION['colaborador_nombre']);
    unset($_SESSION['colaborador_apellido']);
    unset($_SESSION['colaborador_foto']);
    unset($_SESSION['colaborador_usuario']);
}
?>