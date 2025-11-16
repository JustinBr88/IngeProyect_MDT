<?php
include 'loginSesion.php';
include('../navbar_unificado.php');
require_once __DIR__ . '/../conexion.php';

$conexion = new Conexion();
$db = $conexion->getConexion();

// Listar lotes desde tabla `lotes` (no agrupar inventario)
$stmt = $db->prepare("SELECT l.*, c.nombre AS categoria_nombre FROM lotes l LEFT JOIN categorias c ON l.categoria_id = c.id ORDER BY l.id DESC");
$stmt->execute();
$res = $stmt->get_result();
$lotes = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="container-fluid">
  <div class="row px-xl-5">
    <div class="col-12">
      <nav class="breadcrumb bg-light mb-30" aria-label="Ruta de navegación inventario">
        <a class="breadcrumb-item text-dark" href="Home.php">Inicio</a>
        <span class="breadcrumb-item active">Inventario (Lotes)</span>
      </nav>
    </div>
  </div>

  <div class="mb-3 d-flex justify-content-between align-items-center">
    <h3>Lotes</h3>
    <div>
      <button id="btnAdd" class="btn btn-success">Añadir Lote<i class="fas fa-plus"></i></button>
    </div>
  </div>

  <div class="card shadow">
    <div class="card-body">
      <div id="lotesContainer">
        <div class="table-responsive">
          <table class="table table-striped" id="tableLotes">
            <thead>
              <tr>
                <th>Imagen</th>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Marca</th>
                <th>Costo</th>
                <th>Depreciación (meses)</th>
                <th>Cantidad</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tbodyLotes">
              <?php foreach ($lotes as $l): ?>
                <?php
                  $img = !empty($l['imagen']) ? '../uploads/' . $l['imagen'] : '../img/perfil.jpg';
                  if (!file_exists($img)) $img = '../img/perfil.jpg';
                ?>
                <tr>
                  <td><img src="<?= htmlspecialchars($img) ?>" style="width:60px;height:40px;object-fit:cover" alt=""></td>
                  <td><?= htmlspecialchars($l['id']) ?></td>
                  <td><?= htmlspecialchars($l['nombre_equipo']) ?></td>
                  <td><?= htmlspecialchars($l['categoria_nombre'] ?? '') ?></td>
                  <td><?= htmlspecialchars($l['marca'] ?? '') ?></td>
                  <td><?= htmlspecialchars($l['costo'] ?? '') ?></td>
                  <td><?= htmlspecialchars($l['tiempo_depreciacion'] ?? '') ?></td>
                  <td><?= htmlspecialchars($l['cantidad'] ?? 0) ?></td>
                  <td>
                    <button class="btn btn-sm btn-warning btn-detalles" data-id="<?= $l['id'] ?>" data-nombre="<?= htmlspecialchars($l['nombre_equipo']) ?>">Detalles</button>
                    <button class="btn btn-sm btn-primary btn-edit" data-id="<?= $l['id'] ?>">Editar</button>
                    <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $l['id'] ?>">Eliminar</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include_once __DIR__ . '/../modelos.php'; ?>

<!-- Cargar Bootstrap JS: intenta copia local primero, si falla cargamos CDN -->
<?php include('footer.php'); ?>