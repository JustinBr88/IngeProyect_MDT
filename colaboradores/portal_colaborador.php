<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'loginSesionColaborador.php';
require_once(__DIR__ . '/../conexion.php');

// Validación de sesión colaborador
if (!isset($_SESSION['colaborador_logeado']) || !$_SESSION['colaborador_logeado']) {
    header('Location: ../Usuario/Login.php');
    exit;
}
$conexion = new Conexion();

// Recargar datos actualizados del colaborador
$colab = $conexion->obtenerColaboradorPorId($_SESSION['colaborador_id']);

// Registrar acceso en historial
$conexion->registrarAccesoColaborador($colab['id']);
?>
<?php include('../navbar_unificado.php'); ?>

<!-- Breadcrumb Start -->
<div class="container-fluid">
  <div class="row px-xl-5">
    <div class="col-12">
      <nav class="breadcrumb bg-light mb-30" aria-label="Ruta de navegación portal colaborador">
        <a class="breadcrumb-item text-dark" href="portal_colaborador.php">Inicio</a>
        <span class="breadcrumb-item active">Portal Colaborador</span>
      </nav>
    </div>
  </div>
</div>
<!-- Breadcrumb End -->

<div class="container mt-5">
  <div class="row">
    <div class="col-md-4 text-center">
      <img id="foto-perfil-principal" src="../mostrar_foto_usuario.php?tipo=colaborador&id=<?= $_SESSION['colaborador_id'] ?>" alt="Foto Perfil" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit:cover;" />
      <h3><?= htmlspecialchars($colab['nombre'] . ' ' . $colab['apellido']) ?></h3>
      <p class="mb-1"><i class="fa fa-envelope"></i> <?= htmlspecialchars($colab['correo']) ?></p>
      <p class="mb-1"><i class="fa fa-phone"></i> <?= htmlspecialchars($colab['telefono']) ?></p>
      <p class="mb-1"><i class="fa fa-map-marker"></i> <?= htmlspecialchars($colab['ubicacion']) ?></p>
      <p class="mb-1"><i class="fa fa-building"></i> <?= htmlspecialchars($colab['direccion']) ?></p>
      
      <!-- Enlaces de acción -->
      <div class="mt-3">
        <a href="PerfilColab.php" class="btn btn-primary btn-sm mb-2 w-100">
          <i class="fa fa-user-edit"></i> Editar Perfil
        </a>
        <a href="logout_colaborador.php" class="btn btn-danger btn-sm w-100">
          <i class="fa fa-sign-out-alt"></i> Cerrar sesión
        </a>
      </div>
    </div>

    <div class="col-md-8">
      <div class="d-flex mb-3">
        <a href="solicitar_donacion.php" class="btn btn-success">
          <i class="fas fa-heart"></i> Solicitar Donación
        </a>
      </div>
      <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped">
          <thead class="thead-dark">
            <tr>
              <th>Equipo</th>
              <th>Marca</th>
              <th>Modelo</th>
              <th>Serie</th>
              <th>Fecha de Asignación</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $res = $conexion->getConexion()->query(
              "SELECT i.nombre_equipo, i.marca, i.modelo, i.numero_serie, a.fecha_asignacion, a.estado as estado_asignacion
               FROM inventario i
               JOIN asignaciones a ON i.id = a.inventario_id
               WHERE a.colaborador_id = {$colab['id']} AND a.estado IN ('asignado','dañado','donado')"
            );
            if ($res->num_rows > 0) {
              while($eq = $res->fetch_assoc()) {
                echo "<tr>
                  <td>" . htmlspecialchars($eq['nombre_equipo']) . "</td>
                  <td>" . htmlspecialchars($eq['marca']) . "</td>
                  <td>" . htmlspecialchars($eq['modelo']) . "</td>
                  <td>" . htmlspecialchars($eq['numero_serie']) . "</td>
                  <td>" . htmlspecialchars($eq['fecha_asignacion']) . "</td>
                  <td>" . htmlspecialchars($eq['estado_asignacion']) . "</td>
                </tr>";
              }
            } else {
              echo "<tr><td colspan='6'>No tiene equipos asignados actualmente.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>

      <h4 class="mt-5">Historial de Accesos</h4>
      <div class="table-responsive">
        <table class="table table-bordered table-striped">
          <thead class="thead-dark">
            <tr>
              <th>Fecha y Hora</th>
              <th>IP</th>
              <th>Navegador</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $historial = $conexion->obtenerHistorialAccesosColaborador($colab['id']);
            if ($historial && count($historial) > 0) {
              foreach ($historial as $h) {
                echo "<tr>
                  <td>" . htmlspecialchars($h['fecha_hora']) . "</td>
                  <td>" . htmlspecialchars($h['ip']) . "</td>
                  <td>" . htmlspecialchars($h['user_agent']) . "</td>
                </tr>";
              }
            } else {
              echo "<tr><td colspan='3'>No hay historial de accesos registrado.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include('footer.php'); ?>