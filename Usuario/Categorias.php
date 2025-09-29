<?php 
include('loginSesion.php'); // Descomentado para verificar sesión
include('../navbar_unificado.php');
require_once('../conexion.php');

$conexion = new Conexion();
$mensaje = '';
$tipo_mensaje = '';

// Verifica si el usuario es administrador
$es_admin = false;

// Verificar por sesión directamente
if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    $es_admin = true;
}

// Método de respaldo: Verificar por base de datos
if (!$es_admin && isset($_SESSION['id'])) {
    $es_admin = $conexion->esAdministrador($_SESSION['id']);
}

// Procesar acciones CRUD solo si es administrador
if ($es_admin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['crear'])) {
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        
        if ($conexion->insertarCategoria($nombre, $descripcion)) {
            $mensaje = "Categoría creada exitosamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al crear la categoría.";
            $tipo_mensaje = "danger";
        }
    }
    
    if (isset($_POST['editar'])) {
        $id = $_POST['id'];
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        
        if ($conexion->actualizarCategoria($id, $nombre, $descripcion)) {
            $mensaje = "Categoría actualizada exitosamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al actualizar la categoría.";
            $tipo_mensaje = "danger";
        }
    }
    
    if (isset($_POST['eliminar'])) {
        $id = $_POST['id'];
        
        $resultado = $conexion->eliminarCategoria($id);
        if ($resultado === false) {
            $mensaje = "No se puede eliminar la categoría porque está siendo usada en el inventario.";
            $tipo_mensaje = "warning";
        } elseif ($resultado) {
            $mensaje = "Categoría eliminada exitosamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al eliminar la categoría.";
            $tipo_mensaje = "danger";
        }
    }
}
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Categorías</h2>
        <?php if (!$es_admin): ?>
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle"></i> Solo los administradores pueden gestionar categorías.
            </div>
        <?php endif; ?>
    </div>

    <!-- Mostrar mensajes -->
    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
            <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Formulario para crear nueva categoría (solo admins) -->
    <?php if ($es_admin): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-plus"></i> Agregar Nueva Categoría</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-4">
                        <label for="nombre" class="form-label">Nombre de la categoría *</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <input type="text" name="descripcion" id="descripcion" class="form-control">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" name="crear" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Crear
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de Categorías -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-list"></i> Lista de Categorías</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tabla-categorias">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <?php if ($es_admin): ?>
                                <th width="200">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $categorias = $conexion->obtenerCategorias();
                        foreach ($categorias as $categoria):
                        ?>
                        <tr data-id="<?= $categoria['id'] ?>">
                            <td><?= $categoria['id'] ?></td>
                            <td class="editable-nombre"><?= htmlspecialchars($categoria['nombre']) ?></td>
                            <td class="editable-descripcion"><?= htmlspecialchars($categoria['descripcion']) ?></td>
                            <?php if ($es_admin): ?>
                            <td>
                                <button class="btn btn-sm btn-warning btn-editar" data-id="<?= $categoria['id'] ?>">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-sm btn-success btn-guardar d-none" data-id="<?= $categoria['id'] ?>">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                                <button class="btn btn-sm btn-secondary btn-cancelar d-none" data-id="<?= $categoria['id'] ?>">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                                <button class="btn btn-sm btn-danger btn-eliminar" data-id="<?= $categoria['id'] ?>" data-nombre="<?= htmlspecialchars($categoria['nombre']) ?>">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<?php if ($es_admin): ?>
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar la categoría <strong id="categoria-nombre"></strong>?</p>
                <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <form method="POST" id="form-eliminar">
                    <input type="hidden" name="id" id="categoria-id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="eliminar" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Scripts para funcionalidad CRUD -->
<?php if ($es_admin): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edición inline
    document.querySelectorAll('.btn-editar').forEach(btn => {
        btn.addEventListener('click', function() {
            const fila = this.closest('tr');
            const id = this.dataset.id;
            
            // Convertir celdas en inputs
            const nombreCell = fila.querySelector('.editable-nombre');
            const descripcionCell = fila.querySelector('.editable-descripcion');
            
            const nombreOriginal = nombreCell.textContent;
            const descripcionOriginal = descripcionCell.textContent;
            
            nombreCell.innerHTML = `<input type="text" class="form-control" value="${nombreOriginal}" data-original="${nombreOriginal}">`;
            descripcionCell.innerHTML = `<input type="text" class="form-control" value="${descripcionOriginal}" data-original="${descripcionOriginal}">`;
            
            // Mostrar/ocultar botones
            this.classList.add('d-none');
            fila.querySelector('.btn-guardar').classList.remove('d-none');
            fila.querySelector('.btn-cancelar').classList.remove('d-none');
            fila.querySelector('.btn-eliminar').classList.add('d-none');
        });
    });
    
    // Guardar cambios
    document.querySelectorAll('.btn-guardar').forEach(btn => {
        btn.addEventListener('click', function() {
            const fila = this.closest('tr');
            const id = this.dataset.id;
            
            const nombreInput = fila.querySelector('.editable-nombre input');
            const descripcionInput = fila.querySelector('.editable-descripcion input');
            
            const nombre = nombreInput.value.trim();
            const descripcion = descripcionInput.value.trim();
            
            if (!nombre) {
                alert('El nombre de la categoría es obligatorio.');
                return;
            }
            
            // Crear form y enviar
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="editar" value="1">
                <input type="hidden" name="id" value="${id}">
                <input type="hidden" name="nombre" value="${nombre}">
                <input type="hidden" name="descripcion" value="${descripcion}">
            `;
            document.body.appendChild(form);
            form.submit();
        });
    });
    
    // Cancelar edición
    document.querySelectorAll('.btn-cancelar').forEach(btn => {
        btn.addEventListener('click', function() {
            const fila = this.closest('tr');
            
            const nombreCell = fila.querySelector('.editable-nombre');
            const descripcionCell = fila.querySelector('.editable-descripcion');
            
            const nombreOriginal = nombreCell.querySelector('input').dataset.original;
            const descripcionOriginal = descripcionCell.querySelector('input').dataset.original;
            
            nombreCell.textContent = nombreOriginal;
            descripcionCell.textContent = descripcionOriginal;
            
            // Restaurar botones
            fila.querySelector('.btn-editar').classList.remove('d-none');
            this.classList.add('d-none');
            fila.querySelector('.btn-guardar').classList.add('d-none');
            fila.querySelector('.btn-eliminar').classList.remove('d-none');
        });
    });
    
    // Modal de eliminación
    document.querySelectorAll('.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const nombre = this.dataset.nombre;
            
            document.getElementById('categoria-id').value = id;
            document.getElementById('categoria-nombre').textContent = nombre;
            
            new bootstrap.Modal(document.getElementById('modalEliminar')).show();
        });
    });
});
</script>
<?php endif; ?>

<?php include('footer.php'); ?>