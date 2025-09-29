<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determinar el tipo de usuario y rutas base
$es_admin = isset($_SESSION['logeado']) && $_SESSION['logeado'] === true;
$es_colaborador = isset($_SESSION['colaborador_logeado']) && $_SESSION['colaborador_logeado'] === true;

// Detectar si estamos en el directorio colaboradores o Usuario
$current_dir = basename(dirname($_SERVER['SCRIPT_NAME']));
$es_directorio_colaborador = ($current_dir === 'colaboradores');
$es_directorio_usuario = ($current_dir === 'Usuario');

// Configurar rutas base según el directorio actual
if ($es_directorio_colaborador) {
    $base_path = '../';
    $css_path = '../css/';
    $img_path = '../img/';
    $login_path = '../Usuario/Login.php';
} elseif ($es_directorio_usuario) {
    $base_path = '../';
    $css_path = '../css/';
    $img_path = '../img/';
    $login_path = 'Login.php';
} else {
    // Páginas en la raíz del proyecto
    $base_path = '';
    $css_path = 'css/';
    $img_path = 'img/';
    $login_path = 'Usuario/Login.php';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>MD Tecnología</title>
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <!-- Favicon -->
  <link href="<?php echo $img_path; ?>favicon.ico" rel="icon" />
  <!-- Google Web Fonts -->
  <link rel="preconnect" href="https://fonts.gstatic.com" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet" />
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $css_path; ?>style.css">
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
    .dropdown-toggle::after {
      margin-left: 8px !important;
    }
    .navbar-brand-title {
      color: #007bff;
      font-weight: bold;
      text-decoration: none;
    }
    .user-role-badge {
      background: <?php echo $es_admin ? '#28a745' : '#17a2b8'; ?>;
      color: white;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 0.8rem;
      margin-top: 3px;
    }
    .dropdown img {
      transition: transform 0.2s ease;
    }
    .dropdown:hover img {
      transform: scale(1.05);
    }
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
        <a href="<?php echo $es_colaborador ? ($es_directorio_colaborador ? 'portal_colaborador.php' : 'colaboradores/portal_colaborador.php') : ($es_directorio_usuario ? 'Home.php' : ($es_directorio_colaborador ? '../Usuario/Home.php' : 'Usuario/Home.php')); ?>" class="text-decoration-none">
          <img src="<?php echo $img_path; ?>logo.png" alt="logo" />
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
            // Configurar imagen de perfil por defecto
            $foto_default = ($es_directorio_usuario || $es_directorio_colaborador) 
                ? '../img/usuarios/default.jpg' 
                : 'img/usuarios/default.jpg';
            
            $foto = $foto_default; // Foto por defecto
            $nombreUsuario = "Inicia sesión para continuar";
            $rolUsuario = "";

            if ($es_admin) {
                $nombreUsuario = htmlspecialchars($_SESSION['usuario']);
                $rolUsuario = "Administrador";
                // Para admin, usar mostrar_foto_usuario.php si está logueado
                if (isset($_SESSION['id'])) {
                    $mostrar_foto_path = ($es_directorio_usuario) ? '../mostrar_foto_usuario.php' : 'mostrar_foto_usuario.php';
                    $foto = $mostrar_foto_path . '?id=' . $_SESSION['id'] . '&t=' . time();
                }
            } elseif ($es_colaborador) {
                $nombreUsuario = htmlspecialchars($_SESSION['colaborador_nombre'] ?? $_SESSION['colaborador_usuario'] ?? 'Colaborador');
                $rolUsuario = "Colaborador";
                // Para colaborador, usar el sistema de mostrar_foto_usuario.php
                $foto = $base_path . "mostrar_foto_usuario.php?tipo=colaborador&id=" . $_SESSION['colaborador_id'];
            }
          ?>
          <img src="<?php echo htmlspecialchars($foto); ?>" class="profile-pic-navbar-lg" alt="Foto de perfil" 
               onerror="if(!this.hasAttribute('data-error-handled')) { this.setAttribute('data-error-handled', 'true'); this.src='<?php echo $foto_default; ?>'; } else { this.style.display='none'; }">
          <div class="profile-username-navbar">
            <?php echo $nombreUsuario; ?>
            <?php if ($rolUsuario): ?>
              <div class="user-role-badge"><?php echo $rolUsuario; ?></div>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
    
    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <a class="navbar-brand d-lg-none navbar-brand-title" href="<?php echo $es_colaborador ? ($es_directorio_colaborador ? 'portal_colaborador.php' : 'colaboradores/portal_colaborador.php') : ($es_directorio_usuario ? 'Home.php' : ($es_directorio_colaborador ? '../Usuario/Home.php' : 'Usuario/Home.php')); ?>">
          MD Tecnología
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
          aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
          <ul class="navbar-nav me-auto">
            <?php if ($es_admin): ?>
              <!-- Menú para Administradores -->
              <li class="nav-item"><a class="nav-link" href="<?php echo ($es_directorio_colaborador || !$es_directorio_usuario) ? '../Usuario/' : ''; ?>Home.php"><i class="fa fa-home"></i> Inicio</a></li>
              <li class="nav-item"><a class="nav-link" href="<?php echo ($es_directorio_colaborador || !$es_directorio_usuario) ? '../Usuario/' : ''; ?>Inventario.php"><i class="fa fa-boxes"></i> Inventario</a></li>
              <li class="nav-item"><a class="nav-link" href="<?php echo ($es_directorio_colaborador || !$es_directorio_usuario) ? '../Usuario/' : ''; ?>Categorias.php"><i class="fa fa-tags"></i> Categorías</a></li>
              <li class="nav-item"><a class="nav-link" href="<?php echo ($es_directorio_colaborador || !$es_directorio_usuario) ? '../Usuario/' : ''; ?>Usuarios.php"><i class="fa fa-users"></i> Usuarios</a></li>
              <li class="nav-item"><a class="nav-link" href="<?php echo ($es_directorio_colaborador || !$es_directorio_usuario) ? '../Usuario/' : ''; ?>Asignaciones.php"><i class="fa fa-hand-holding"></i> Asignaciones</a></li>
              <li class="nav-item"><a class="nav-link" href="<?php echo ($es_directorio_colaborador || !$es_directorio_usuario) ? '../Usuario/' : ''; ?>Solicitudes.php"><i class="fa fa-list"></i> Solicitudes</a></li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa fa-cogs"></i> Gestión
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
                  <li><a class="dropdown-item" href="<?php echo ($es_directorio_colaborador || !$es_directorio_usuario) ? '../Usuario/' : ''; ?>Descarte.php"><i class="fa fa-trash"></i> Descarte</a></li>
                  <li><a class="dropdown-item" href="<?php echo ($es_directorio_colaborador || !$es_directorio_usuario) ? '../Usuario/' : ''; ?>gestionar_donaciones.php"><i class="fa fa-heart"></i> Gestionar Donaciones</a></li>
                  <li><a class="dropdown-item" href="<?php echo ($es_directorio_colaborador || !$es_directorio_usuario) ? '../Usuario/' : ''; ?>Reportes.php"><i class="fa fa-chart-bar"></i> Reportes</a></li>
                </ul>
              </li>
            <?php elseif ($es_colaborador): ?>
              <!-- Menú para Colaboradores -->
              <li class="nav-item"><a class="nav-link" href="<?php echo $es_directorio_colaborador ? '' : 'colaboradores/'; ?>portal_colaborador.php"><i class="fa fa-home"></i> Inicio</a></li>
              <li class="nav-item"><a class="nav-link" href="<?php echo $es_directorio_colaborador ? '' : 'colaboradores/'; ?>InventarioColab.php"><i class="fa fa-boxes"></i> Inventario</a></li>
              <li class="nav-item"><a class="nav-link" href="<?php echo $es_directorio_colaborador ? '' : 'colaboradores/'; ?>SolicitudesColab.php"><i class="fa fa-list"></i> Solicitudes</a></li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownColab" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa fa-heart"></i> Donaciones
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownColab">
                  <li><a class="dropdown-item" href="<?php echo $es_directorio_colaborador ? '' : 'colaboradores/'; ?>solicitar_donacion.php"><i class="fa fa-heart"></i> Solicitar Donación</a></li>
                </ul>
              </li>
            <?php else: ?>
              <!-- Menú para usuarios no logueados -->
              <li class="nav-item"><a class="nav-link" href="<?php echo $login_path; ?>"><i class="fa fa-sign-in-alt"></i> Iniciar Sesión</a></li>
            <?php endif; ?>
          </ul>
          
          <ul class="navbar-nav ms-auto">
            <?php if ($es_admin): ?>
              <!-- Dropdown de usuario para Admin -->
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-light" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa fa-user"></i> <?php echo $nombreUsuario; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                  <li><a class="dropdown-item" href="<?php echo ($es_directorio_colaborador || !$es_directorio_usuario) ? '../Usuario/' : ''; ?>Perfil.php"><i class="fa fa-user me-2"></i>Mi Perfil</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item text-danger" href="<?php echo ($es_directorio_colaborador || !$es_directorio_usuario) ? '../Usuario/' : ''; ?>logout.php"><i class="fa fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                </ul>
              </li>
            <?php elseif ($es_colaborador): ?>
              <!-- Enlaces simples para Colaboradores -->
              <li class="nav-item">
                <a class="nav-link text-light" href="<?php echo $es_directorio_colaborador ? '' : 'colaboradores/'; ?>PerfilColab.php"><i class="fa fa-user"></i> Perfil</a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-danger font-weight-bold" href="<?php echo $es_directorio_colaborador ? '' : 'colaboradores/'; ?>logout.php"><i class="fa fa-sign-out-alt"></i> Cerrar sesión</a>
              </li>
            <?php else: ?>
              <li class="nav-item">
                <a class="nav-link text-primary font-weight-bold" href="<?php echo $login_path; ?>"><i class="fa fa-sign-in-alt"></i> Iniciar sesión</a>
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
    document.addEventListener('DOMContentLoaded', function() {
      // Inicialización de dropdowns
      const dropdowns = document.querySelectorAll('.dropdown-toggle');
      
      dropdowns.forEach(dropdown => {
        // Verificar si Bootstrap está disponible
        if (typeof bootstrap !== 'undefined') {
          try {
            new bootstrap.Dropdown(dropdown);
          } catch (error) {
            console.error('Error inicializando dropdown:', error);
          }
        }
        
        // Event listener manual como fallback
        dropdown.addEventListener('click', function(e) {
          e.preventDefault();
          
          const menu = this.nextElementSibling;
          if (menu && menu.classList.contains('dropdown-menu')) {
            const isVisible = menu.style.display === 'block';
            
            // Cerrar todos los otros dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(m => {
              m.style.display = 'none';
            });
            
            // Toggle el actual
            menu.style.display = isVisible ? 'none' : 'block';
          }
        });
      });
      
      // Cerrar dropdowns al hacer clic fuera
      document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
          document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.style.display = 'none';
          });
        }
      });
    });
  </script>
</body>
</html>
