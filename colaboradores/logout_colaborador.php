<?php
session_start();
// Elimina todas las variables de sesión del colaborador
unset($_SESSION['colaborador_logeado']);
unset($_SESSION['colaborador_id']);
unset($_SESSION['colaborador_nombre']);
unset($_SESSION['colaborador_foto']);

// Opcional: destruye toda la sesión si no necesitas otras variables
// session_destroy();

// Redirige al login de colaborador
header('Location: ../Usuario/Login.php');
exit;
?>