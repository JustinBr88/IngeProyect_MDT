<?php include('../navbar_unificado.php'); ?>
<!-- Breadcrumb Start -->
<div class="container-fluid">
  <div class="row px-xl-5">
    <div class="col-12">
      <nav class="breadcrumb bg-light mb-30" aria-label="Ruta de navegación iniciar sesión">
        <a class="breadcrumb-item text-dark" href="Home.php">Inicio</a>
        <span class="breadcrumb-item active">Login</span>
      </nav>
    </div>
  </div>
</div>

<!--formulario inicio sesion-->
<div class="container-fluid d-flex justify-content-center" style="margin-top: 60px;">
  <div class="col-md-5">
    <div class="text-center mb-4">
      <h2>Sistema CMDB - MD Tecnología</h2>
      <p class="text-muted">Portal Unificado de Acceso</p>
    </div>
    
    <div class="card shadow">
      <div class="card-body">
        <div class="text-center mb-4">
          <img src="../img/logoMDT.png" alt="MD Tecnología" style="max-width: 150px;">
        </div>
        
        <div class="alert alert-info">
          <i class="fas fa-info-circle"></i>
          <strong>Acceso para:</strong>
          <ul class="mb-0 mt-2">
            <li><strong>Administradores y Técnicos:</strong> Gestión completa del inventario</li>
            <li><strong>Colaboradores:</strong> Consulta de equipos y solicitudes</li>
          </ul>
        </div>
        
        <form id="loginForm" method="POST">
          <div class="mb-3">
            <label for="username" class="form-label">Usuario / Correo</label>
            <input
              type="text"
              id="username"
              class="form-control"
              placeholder="Ingresa tu usuario o correo"
              required
            />
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <div class="input-group">
              <input
                type="password"
                id="password"
                class="form-control"
                placeholder="Ingresa tu contraseña"
                required
              />
              <span class="input-group-text" style="cursor: pointer;">
                <img
                  src="../img/eye-slash.webp"
                  alt="Mostrar/Ocultar"
                  style="width: 20px; height: 20px"
                  class="toggle-password"
                  data-target="password"
                />
              </span>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100 mt-3">
            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
          </button>
        </form>
        
        <div class="text-center mt-4">
          <small class="text-muted">
            ¿Problemas para acceder? Contacta al administrador del sistema.
          </small>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Login End -->
<?php include('footer.php'); ?>
<!-- JavaScript -->
<script src="../js/login.js"></script>