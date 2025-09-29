<?php 
include 'loginSesion.php';
include('../navbar_unificado.php');
require_once '../conexion.php';
$conexion = new Conexion();
$result = $conexion->obtenerInventario();
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Inventario - Equipos y Software</h2>
         <div>
            <a href="Solicitudes.php" class="btn btn-success me-2">Solicitudes</a>
            <a href="Asignaciones.php" class="btn btn-primary me-2">Asignaciones</a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevo">Añadir Nuevo</button>
        </div>
    </div>
    <!-- Tabla de Inventario -->
    <table class="table table-bordered table-striped mt-4" id="tabla-inventario">
        <thead class="thead-dark">
            <tr>
                <th>Imagen</th>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Serie</th>
                <th>Costo</th>
                <th>Ingreso</th>
                <th>Depreciación</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result as $row): ?>
            <tr data-id="<?= htmlspecialchars($row['id']) ?>">
                <td>
                    <?php
                    // Verificar si existe imagen en la base de datos y el archivo existe
                    $imagenPath = '../img/perfil.jpg'; // Imagen por defecto
                    if (!empty($row['imagen'])) {
                        $imagenEquipo = '../uploads/' . $row['imagen'];
                        // Verificar si el archivo existe
                        if (file_exists($imagenEquipo)) {
                            $imagenPath = $imagenEquipo;
                        }
                    }
                    ?>
                    <img src='<?= htmlspecialchars($imagenPath) ?>' width='60' alt='Imagen equipo' class='img-thumbnail' 
                         onerror="this.src='../img/perfil.jpg';" style="height: 60px; object-fit: cover;">
                </td>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td class="editable" data-campo="nombre_equipo"><?= htmlspecialchars($row['nombre_equipo']) ?></td>
                <td class="editable" data-campo="categoria_id"><?= htmlspecialchars($row['categoria']) ?></td>
                <td class="editable" data-campo="marca"><?= htmlspecialchars($row['marca']) ?></td>
                <td class="editable" data-campo="modelo"><?= htmlspecialchars($row['modelo']) ?></td>
                <td class="editable" data-campo="numero_serie"><?= htmlspecialchars($row['numero_serie']) ?></td>
                <td class="editable" data-campo="costo"><?= htmlspecialchars($row['costo']) ?></td>
                <td class="editable" data-campo="fecha_ingreso"><?= htmlspecialchars($row['fecha_ingreso']) ?></td>
                <td class="editable" data-campo="tiempo_depreciacion"><?= htmlspecialchars($row['tiempo_depreciacion']) ?></td>
                <td class="editable" data-campo="estado"><?= htmlspecialchars($row['estado']) ?></td>
                <td>
                    <button class="btn btn-sm btn-warning btn-editar">Editar</button>
                    <button class="btn btn-sm btn-success btn-guardar d-none">Guardar</button>
                    <button class="btn btn-sm btn-danger btn-eliminar">Eliminar</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal para añadir nuevo equipo/software -->
<div class="modal fade" id="modalNuevo" tabindex="-1" aria-labelledby="modalNuevoLabel">
    <div class="modal-dialog">
        <form id="formNuevo" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevoLabel">Añadir Equipo/Software</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="nombre_equipo" class="form-label">Nombre del equipo *</label>
                    <input type="text" name="nombre_equipo" id="nombre_equipo" class="form-control" required maxlength="100">
                </div>
                <div class="mb-3">
                    <label for="categoria_id" class="form-label">Categoría *</label>
                    <select name="categoria_id" id="categoria_id" class="form-control" required>
                        <option value="">Seleccione una categoría</option>
                        <?php
                        $categorias = $conexion->obtenerCategorias();
                        foreach ($categorias as $cat) {
                            echo "<option value='" . htmlspecialchars($cat['id']) . "'>" . htmlspecialchars($cat['nombre']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="marca" class="form-label">Marca</label>
                    <input type="text" name="marca" id="marca" class="form-control" maxlength="50">
                </div>
                <div class="mb-3">
                    <label for="modelo" class="form-label">Modelo</label>
                    <input type="text" name="modelo" id="modelo" class="form-control" maxlength="50">
                </div>
                <div class="mb-3">
                    <label for="numero_serie" class="form-label">Número de Serie</label>
                    <input type="text" name="numero_serie" id="numero_serie" class="form-control" maxlength="50">
                </div>
                <div class="mb-3">
                    <label for="costo" class="form-label">Costo</label>
                    <input type="number" step="0.01" name="costo" id="costo" class="form-control" min="0" max="999999.99">
                </div>
                <div class="mb-3">
                    <label for="fecha_ingreso" class="form-label">Fecha de ingreso</label>
                    <input type="date" name="fecha_ingreso" id="fecha_ingreso" class="form-control" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="mb-3">
                    <label for="tiempo_depreciacion" class="form-label">Tiempo de depreciación (meses)</label>
                    <input type="number" name="tiempo_depreciacion" id="tiempo_depreciacion" class="form-control" min="0" max="120">
                </div>
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-control">
                        <option value="activo">Activo</option>
                        <option value="baja">Baja</option>
                        <option value="inventario">Inventario</option>
                        <option value="reparacion">Reparación</option>
                        <option value="descarte">Descarte</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="imagen" class="form-label">Imagen del equipo/software</label>
                    <input type="file" name="imagen" id="imagen" class="form-control" accept="image/jpeg,image/jpg,image/png,image/gif" maxlength="5242880">
                    <div class="form-text">
                        <small>Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Incluir modales requeridos -->
<?php include('../modelos.php'); ?>

<!-- Scripts sanitizados -->
<script src="../js/modales.js"></script>
<script src="../js/botonEditar_Guardar.js"></script>

<!-- Script adicional para validación de formularios -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación adicional en el cliente
    const formNuevo = document.getElementById('formNuevo');
    const imagenInput = document.getElementById('imagen');
    
    // Validar archivo de imagen
    imagenInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            // Validar tamaño (5MB máximo)
            if (file.size > 5242880) {
                alert('El archivo es demasiado grande. Máximo 5MB.');
                this.value = '';
                return;
            }
            
            // Validar tipo de archivo
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Tipo de archivo no permitido. Solo JPG, PNG y GIF.');
                this.value = '';
                return;
            }
        }
    });
    
    // Sanitizar inputs de texto en tiempo real
    const textInputs = formNuevo.querySelectorAll('input[type="text"]');
    textInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Remover caracteres potencialmente peligrosos
            this.value = this.value.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
            this.value = this.value.replace(/[<>]/g, '');
        });
    });
    
    // Validar números
    const numberInputs = formNuevo.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value < 0) this.value = 0;
        });
    });
});
</script>

<?php include('footer.php'); ?>