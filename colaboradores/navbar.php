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
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap"
    rel="stylesheet"
  />
  <!-- Font Awesome -->
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    rel="stylesheet"
  />
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
  </style>
</head>
<body>
  <!-- Header Start -->
  <header>
    <div class="row align-items-center bg-light py-3 px-xl-5 d-none d-lg-flex" style="margin-right: 0px">
      <div class="col-lg-4">
        <a href="../Usuario/Home.php" class="text-decoration-none">
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
            // Si el usuario está logueado y tiene foto, mostrarla. Sino, mostrar la default.
            if(isset($_SESSION['logeado']) && $_SESSION['logeado'] === true && !empty($_SESSION['foto'])) {
                $foto = $_SESSION['foto'];
            } else {
                $foto = '../img/default_profile.png';
            }
            // Nombre del usuario, si está logueado
            if(isset($_SESSION['logeado']) && $_SESSION['logeado'] === true && !empty($_SESSION['usuario'])) {
                $nombreUsuario = htmlspecialchars($_SESSION['usuario']);
            } else {
                $nombreUsuario = "Inicia sesión para continuar";
            }
          ?>
          <img src="<?php echo htmlspecialchars($foto); ?>" class="profile-pic-navbar-lg" alt="Foto de perfil">
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
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent"
          aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item"><a class="nav-link" href="Home.php"><i class="fa fa-home"></i> Inicio</a></li>
            <li class="nav-item"><a class="nav-link" href="InventarioColab.php"><i class="fa fa-boxes"></i> Inventario</a></li>
            <li class="nav-item"><a class="nav-link" href="SolicitudesColab.php"><i class="fa fa-list"></i> Solicitudes</a></li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-heart"></i> Donaciones
              </a>
              <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="solicitar_donacion.php"><i class="fa fa-heart"></i> Solicitar Donación</a></li>
              </ul>
            </li>
          </ul>
          <ul class="navbar-nav ml-auto">
            <?php if(isset($_SESSION['colaborador_logeado']) && $_SESSION['colaborador_logeado'] === true): ?>
              <li class="nav-item">
                <a class="nav-link text-light" href="PerfilColab.php"><i class="fa fa-user"></i> Perfil</a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-danger font-weight-bold" href="logout.php"><i class="fa fa-sign-out-alt"></i> Cerrar sesión</a>
              </li>
            <?php else: ?>
              <li class="nav-item">
                <a class="nav-link text-primary font-weight-bold" href="../Usuario/Login.php"><i class="fa fa-sign-in-alt"></i> Iniciar sesión</a>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>
    <!-- Navbar End -->
  </header>
  <!-- Header End -->