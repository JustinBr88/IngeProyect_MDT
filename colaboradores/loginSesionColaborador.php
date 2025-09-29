<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['colaborador_logeado']) || $_SESSION['colaborador_logeado'] !== true) {
    header('Location: ../Usuario/Login.php');
    exit;
}
?>