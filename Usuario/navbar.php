<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>MD Tecnología</title>
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <!-- Favicon -->
  <link href="../img/favicon.ico" rel="icon" />
  <!-- Google Web Fonts -->
  <link rel="preconnect" href="https://fonts.gstatic.com" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet" />
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .profile-pic-navbar-lg {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid #007bff;
      background: #fff;
      display: block;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .profile-pic-wrapper {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-end;
      height: 100%;
    }
    .profile-username-navbar {
      margin-top: 7px;
      font-weight: bold;
      font-size: 1.1rem;
      color: #222;
      text-align: center;
    }
    /* Estilos para el dropdown del usuario - simplificados */
    .dropdown-toggle::after {
      margin-left: 8px !important;
    }
    
    .dropdown img {
      transition: transform 0.2s ease;
    }
    
    .dropdown:hover img {
      transform: scale(1.05);
    }
    
    /* Estilos para el dropdown menu personalizado */
    .dropdown-menu {
      position: absolute !important;
      top: 100% !important;
      right: 0 !important;
      z-index: 1000 !important;
      min-width: 180px;
      background-color: #fff;
      border: 1px solid rgba(0,0,0,.15);
      border-radius: 0.375rem;
      box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
    }
  </style>
</head>
<body>
  <!-- Header Start -->
  <header>
    <div class="row align-items-center bg-light py-3 px-xl-5 d-none d-lg-flex" style="margin-right: 0px">
      <div class="col-lg-4">
        <a href="Home.php" class="text-decoration-none">
          <img src="../img/logo.png" alt="logo" />
        </a>
      </div>
      <div class="col-lg-4 col-6 text-left">
        <form id="searchForm" action="https://www.google.com/search" method="get" target="_blank">
          <div class="input-group">
            <input type="text" class="form-control" name="q" placeholder="Buscar en Google..." required />
            <div class="input-group-append">
              <button id="clearButton" type="button" class="input-group-text bg-transparent text-primary" style="border: none; cursor: pointer">
                <i class="fa fa-search"></i>
              </button>
            </div>
          </div>
        </form>
      </div>
      <!-- Foto de perfil o imagen por defecto + nombre o mensaje -->
      <div class="col-lg-4 col-6 text-right">
        <div class="profile-pic-wrapper">
          <?php
            if(isset($_SESSION['logeado']) && $_SESSION['logeado'] === true && !empty($_SESSION['usuario'])) {
                $nombreUsuario = htmlspecialchars($_SESSION['usuario']);
            } else {
                $nombreUsuario = "Inicia sesión para continuar";
            }
          ?>
          <img src="../mostrar_foto_usuario.php" class="profile-pic-navbar-lg" alt="Foto de perfil">
          <div class="profile-username-navbar">
            <?php echo $nombreUsuario; ?>
          </div>
        </div>
      </div>
    </div>
    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <a class="navbar-brand d-lg-none" href="Home.php">MD Tecnología</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
          aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
          <ul class="navbar-nav me-auto">
            <li class="nav-item"><a class="nav-link" href="Home.php"><i class="fa fa-home"></i> Inicio</a></li>
            <li class="nav-item"><a class="nav-link" href="Inventario.php"><i class="fa fa-boxes"></i> Inventario</a></li>
            <li class="nav-item"><a class="nav-link" href="Categorias.php"><i class="fa fa-list"></i> Categorías</a></li>
            <li class="nav-item"><a class="nav-link" href="Asignaciones.php"><i class="fa fa-users"></i> Asignaciones</a></li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="gestionDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-cog"></i> Gestión
              </a>
              <ul class="dropdown-menu" aria-labelledby="gestionDropdown">
                <li><a class="dropdown-item" href="Descarte.php"><i class="fa fa-trash-alt"></i> Descartes</a></li>
                <li><a class="dropdown-item" href="gestionar_donaciones.php"><i class="fa fa-heart"></i> Gestionar Donaciones</a></li>
              </ul>
            </li>
            <li class="nav-item"><a class="nav-link" href="Reportes.php"><i class="fa fa-chart-bar"></i> Reportes</a></li>
            <li class="nav-item"><a class="nav-link" href="Usuarios.php"><i class="fa fa-user-cog"></i> Usuarios</a></li>
          </ul>
          <ul class="navbar-nav ms-auto">
            <?php if(isset($_SESSION['logeado']) && $_SESSION['logeado'] === true): ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center text-light" href="#" id="userDropdown" role="button" onclick="toggleDropdown(event)">
                  <!-- Mostrar foto desde la base de datos -->
                  <img src="../mostrar_foto_usuario.php" alt="Foto de perfil" class="rounded-circle me-2" 
                       width="35" height="35" style="object-fit: cover; border: 2px solid #fff;">
                  <span><?= htmlspecialchars($_SESSION['usuario'] ?? 'Usuario') ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" id="userDropdownMenu" style="display: none;">
                  <li><a class="dropdown-item" href="Perfil.php"><i class="fa fa-user me-2"></i>Mi Perfil</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                </ul>
              </li>
            <?php else: ?>
              <li class="nav-item">
                <a class="nav-link text-primary font-weight-bold" href="Login.php"><i class="fa fa-sign-in-alt"></i> Iniciar sesión</a>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>
    <!-- Navbar End -->
  </header>
  <!-- Header End -->
  
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Función para manejar el dropdown del usuario
    function toggleDropdown(event) {
      event.preventDefault();
      const menu = document.getElementById('userDropdownMenu');
      const isVisible = menu.style.display === 'block';
      
      // Cerrar todos los dropdowns abiertos
      document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = 'none');
      
      // Toggle del dropdown actual
      menu.style.display = isVisible ? 'none' : 'block';
    }
    
    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', function(event) {
      const dropdown = document.getElementById('userDropdown');
      const menu = document.getElementById('userDropdownMenu');
      
      if (!dropdown.contains(event.target)) {
        menu.style.display = 'none';
      }
    });
    
    // Cerrar dropdown al hacer clic en un enlace
    document.querySelectorAll('.dropdown-item').forEach(item => {
      item.addEventListener('click', function() {
        document.getElementById('userDropdownMenu').style.display = 'none';
      });
    });
  </script>