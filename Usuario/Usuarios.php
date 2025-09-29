<?php 
include('../navbar_unificado.php'); 
require_once('../conexion.php');

$conexion = new Conexion();
$mensaje = '';

// Procesar formulario de creación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear'])) {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $rol = $_POST['rol'];
    $contrasena = $_POST['contrasena'];
    $activo = (int)$_POST['activo'];
    
    // Validar campos
    if (empty($nombre) || empty($correo) || empty($contrasena)) {
        $mensaje = "<div class='alert alert-danger'>Todos los campos obligatorios deben estar llenos.</div>";
    } else {
        // Verificar si el correo ya existe
        $usuarioExiste = $conexion->verificarUsuarioExiste($correo);
        if ($usuarioExiste) {
            $mensaje = "<div class='alert alert-danger'>Ya existe un usuario con ese correo electrónico.</div>";
        } else {
            // Crear usuario
            $resultado = $conexion->crearUsuario($nombre, $correo, $rol, $contrasena, $activo);
            if ($resultado) {
                $mensaje = "<div class='alert alert-success'>Usuario creado exitosamente.</div>";
            } else {
                $mensaje = "<div class='alert alert-danger'>Error al crear el usuario.</div>";
            }
        }
    }
}

// Procesar formulario de modificación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modificar'])) {
    $id = (int)$_POST['id'];
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $rol = $_POST['rol'];
    $activo = (int)$_POST['activo'];
    
    // Validar campos
    if (empty($nombre) || empty($correo)) {
        $mensaje = "<div class='alert alert-danger'>Nombre y correo son obligatorios.</div>";
    } else {
        // Verificar si el correo ya existe (excluyendo el usuario actual)
        $usuarioExiste = $conexion->verificarCorreoDuplicado($correo, $id);
        if ($usuarioExiste) {
            $mensaje = "<div class='alert alert-danger'>Ya existe otro usuario con ese correo electrónico.</div>";
        } else {
            // Actualizar usuario
            $contrasena = !empty($_POST['contrasena']) ? $_POST['contrasena'] : null;
            $resultado = $conexion->actualizarUsuario($id, $nombre, $correo, $rol, $activo, $contrasena);
            if ($resultado) {
                $mensaje = "<div class='alert alert-success'>Usuario actualizado exitosamente.</div>";
            } else {
                $mensaje = "<div class='alert alert-danger'>Error al actualizar el usuario.</div>";
            }
        }
    }
}
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users"></i> Gestión de Usuarios del Sistema</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
            <i class="fas fa-user-plus"></i> Agregar Usuario
        </button>
    </div>
    
    <?= $mensaje ?>
    
    <!-- Tabla de Usuarios -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-list"></i> Usuarios Registrados</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Fecha Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conexion->obtenerUsuarios();
                        foreach ($result as $row) {
                            $estado = $row['activo'] ? 
                                "<span class='badge bg-success'>Activo</span>" : 
                                "<span class='badge bg-danger'>Inactivo</span>";
                            $rolBadge = $row['rol'] === 'admin' ? 
                                "<span class='badge bg-primary'>Administrador</span>" : 
                                "<span class='badge bg-info'>Colaborador</span>";
                            
                            echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['nombre']}</td>
                                <td>{$row['correo']}</td>
                                <td>$rolBadge</td>
                                <td>$estado</td>
                                <td>" . date('d/m/Y', strtotime($row['fecha_creacion'])) . "</td>
                                <td>
                                    <button class='btn btn-sm btn-warning btn-editar' 
                                            data-id='{$row['id']}'
                                            data-nombre='{$row['nombre']}'
                                            data-correo='{$row['correo']}'
                                            data-rol='{$row['rol']}'
                                            data-activo='{$row['activo']}'
                                            data-bs-toggle='modal' 
                                            data-bs-target='#modalEditarUsuario'>
                                        <i class='fas fa-edit'></i> Editar
                                    </button>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Usuario -->
<div class="modal fade" id="modalCrearUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Agregar Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Correo Electrónico *</label>
                            <input type="email" name="correo" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rol *</label>
                            <select name="rol" class="form-control" required>
                                <option value="admin">Administrador</option>
                                <option value="colab">Colaborador</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado</label>
                            <select name="activo" class="form-control">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Contraseña *</label>
                            <input type="password" name="contrasena" class="form-control" required 
                                   minlength="6" placeholder="Mínimo 6 caracteres">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="crear" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-edit"></i> Modificar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre" id="edit-nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Correo Electrónico *</label>
                            <input type="email" name="correo" id="edit-correo" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rol *</label>
                            <select name="rol" id="edit-rol" class="form-control" required>
                                <option value="admin">Administrador</option>
                                <option value="colab">Colaborador</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado</label>
                            <select name="activo" id="edit-activo" class="form-control">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Nueva Contraseña (opcional)</label>
                            <input type="password" name="contrasena" class="form-control" 
                                   minlength="6" placeholder="Dejar vacío para mantener la actual">
                            <small class="text-muted">Solo llena este campo si deseas cambiar la contraseña</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="modificar" class="btn btn-warning">
                        <i class="fas fa-save"></i> Actualizar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Script para cargar datos en el modal de edición
document.addEventListener('DOMContentLoaded', function() {
    const botonesEditar = document.querySelectorAll('.btn-editar');
    
    botonesEditar.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit-id').value = this.dataset.id;
            document.getElementById('edit-nombre').value = this.dataset.nombre;
            document.getElementById('edit-correo').value = this.dataset.correo;
            document.getElementById('edit-rol').value = this.dataset.rol;
            document.getElementById('edit-activo').value = this.dataset.activo;
        });
    });
});
</script>

<?php include('footer.php'); ?>