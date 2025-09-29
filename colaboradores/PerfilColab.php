<?php 
include 'loginSesionColaborador.php';
include('../navbar_unificado.php');
require_once(__DIR__ . '/../conexion.php');

$conexion = new Conexion();
$colaborador_id = $_SESSION['colaborador_id'];

// Obtener datos del colaborador
$colaborador = $conexion->obtenerColaboradorPorId($colaborador_id);

if (!$colaborador) {
    header('Location: ../Usuario/Login.php');
    exit;
}

// Procesar edición de perfil
$msg_perfil = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_perfil'])) {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $ubicacion = trim($_POST['ubicacion']);

    // Validar correo duplicado (excluyendo el suyo)
    if ($conexion->correoDuplicadoColaborador($correo, $colaborador_id)) {
        $msg_perfil = "<div class='alert alert-danger'>El correo ya está registrado por otro colaborador.</div>";
    } else {
        // Actualizar perfil
        $ok = $conexion->actualizarPerfilColaborador(
            $colaborador_id,
            $nombre, $apellido, $correo, $telefono, $direccion, $ubicacion, null
        );

        if ($ok) {
            $msg_perfil = "<div class='alert alert-success'>Perfil actualizado correctamente.</div>";
            // Recargar datos actualizados
            $colaborador = $conexion->obtenerColaboradorPorId($colaborador_id);
        } else {
            $msg_perfil = "<div class='alert alert-danger'>Error al actualizar el perfil.</div>";
        }
    }
}

// Procesar cambio de contraseña si es necesario
$msg_password = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    $password_actual = $_POST['password_actual'] ?? '';
    $password_nueva = $_POST['password_nueva'] ?? '';
    $password_confirmar = $_POST['password_confirmar'] ?? '';
    
    // Validar campos vacíos
    if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
        $msg_password = "<div class='alert alert-danger'>Todos los campos son obligatorios.</div>";
    } elseif ($password_nueva !== $password_confirmar) {
        $msg_password = "<div class='alert alert-danger'>Las contraseñas nuevas no coinciden.</div>";
    } elseif (strlen($password_nueva) < 6) {
        $msg_password = "<div class='alert alert-danger'>La contraseña debe tener al menos 6 caracteres.</div>";
    } else {
        // Verificar contraseña actual
        if ($conexion->verificarPasswordColaborador($colaborador_id, $password_actual)) {
            if ($conexion->cambiarPasswordColaborador($colaborador_id, $password_nueva)) {
                $msg_password = "<div class='alert alert-success'>Contraseña cambiada exitosamente.</div>";
            } else {
                $msg_password = "<div class='alert alert-danger'>Error al cambiar la contraseña.</div>";
            }
        } else {
            $msg_password = "<div class='alert alert-danger'>La contraseña actual es incorrecta.</div>";
        }
    }
}
?>

<div class="container-fluid pt-4 px-4">
    <div class="row">
        <!-- Información del Perfil -->
        <div class="col-xl-8">
            <div class="bg-light rounded h-100 p-4">
                <h6 class="mb-4">Mi Perfil</h6>
                
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="profile-photo-container mb-3">
                            <img id="foto-perfil-principal" src="../mostrar_foto_usuario.php?tipo=colaborador&id=<?= $colaborador_id ?>" 
                                 class="rounded-circle" 
                                 width="150" height="150" 
                                 style="object-fit: cover; border: 4px solid #007bff;"
                                 onerror="if(this.src !== '../img/usuarios/default.jpg') this.src='../img/usuarios/default.jpg'; else this.style.display='none';">
                        </div>
                        <h5><?php echo htmlspecialchars($colaborador['nombre'] . ' ' . $colaborador['apellido']); ?></h5>
                        <p class="text-muted">Colaborador</p>
                        <span class="badge bg-info">Activo</span>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Información Personal</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Nombre:</strong></div>
                                    <div class="col-sm-8"><?php echo htmlspecialchars($colaborador['nombre']); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Apellido:</strong></div>
                                    <div class="col-sm-8"><?php echo htmlspecialchars($colaborador['apellido']); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Identificación:</strong></div>
                                    <div class="col-sm-8"><?php echo htmlspecialchars($colaborador['identificacion']); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Correo:</strong></div>
                                    <div class="col-sm-8"><?php echo htmlspecialchars($colaborador['correo']); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Teléfono:</strong></div>
                                    <div class="col-sm-8"><?php echo htmlspecialchars($colaborador['telefono'] ?? 'No especificado'); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Dirección:</strong></div>
                                    <div class="col-sm-8"><?php echo htmlspecialchars($colaborador['direccion'] ?? 'No especificada'); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Departamento:</strong></div>
                                    <div class="col-sm-8"><?php echo htmlspecialchars($colaborador['departamento_nombre'] ?? 'Sin asignar'); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editarPerfilModal">
                                <i class="fa fa-edit me-2"></i>Editar Perfil
                            </button>
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#cambiarFotoModal">
                                <i class="fa fa-camera me-2"></i>Cambiar Foto
                            </button>
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#cambiarPasswordModal">
                                <i class="fa fa-key me-2"></i>Cambiar Contraseña
                            </button>
                            <a href="portal_colaborador.php" class="btn btn-secondary">
                                <i class="fa fa-arrow-left me-2"></i>Volver al Portal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas -->
        <div class="col-xl-4">
            <div class="bg-light rounded h-100 p-4">
                <h6 class="mb-4">Mis Estadísticas</h6>
                
                <?php
                // Obtener estadísticas del colaborador
                $stats = $conexion->obtenerEstadisticasColaborador($colaborador_id);
                ?>
                
                <div class="d-flex align-items-center border-bottom py-3">
                    <div class="w-100 ms-3">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-0">Solicitudes Pendientes</h6>
                            <small class="text-warning"><?php echo $stats['solicitudes_pendientes'] ?? 0; ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex align-items-center border-bottom py-3">
                    <div class="w-100 ms-3">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-0">Solicitudes Aprobadas</h6>
                            <small class="text-success"><?php echo $stats['solicitudes_aprobadas'] ?? 0; ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex align-items-center border-bottom py-3">
                    <div class="w-100 ms-3">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-0">Equipos Asignados</h6>
                            <small class="text-info"><?php echo $stats['equipos_asignados'] ?? 0; ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex align-items-center py-3">
                    <div class="w-100 ms-3">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-0">Entregas Realizadas</h6>
                            <small class="text-primary"><?php echo $stats['entregas_realizadas'] ?? 0; ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Perfil -->
<div class="modal fade" id="editarPerfilModal" tabindex="-1" aria-labelledby="editarPerfilModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarPerfilModalLabel">Editar Perfil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php echo $msg_perfil; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?php echo htmlspecialchars($colaborador['nombre']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellido" class="form-label">Apellido *</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" 
                                   value="<?php echo htmlspecialchars($colaborador['apellido']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="correo" class="form-label">Correo Electrónico *</label>
                            <input type="email" class="form-control" id="correo" name="correo" 
                                   value="<?php echo htmlspecialchars($colaborador['correo']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" 
                                   value="<?php echo htmlspecialchars($colaborador['telefono'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" 
                                   value="<?php echo htmlspecialchars($colaborador['direccion'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ubicacion" class="form-label">Ubicación</label>
                            <input type="text" class="form-control" id="ubicacion" name="ubicacion" 
                                   value="<?php echo htmlspecialchars($colaborador['ubicacion'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="editar_perfil" class="btn btn-primary">
                        <i class="fa fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cambiar Foto -->
<div class="modal fade" id="cambiarFotoModal" tabindex="-1" aria-labelledby="cambiarFotoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cambiarFotoModalLabel">Cambiar Foto de Perfil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img id="preview-foto" src="../mostrar_foto_usuario.php?tipo=colaborador&id=<?= $colaborador_id ?>" 
                         alt="Foto de perfil" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                </div>
                
                <form id="form-foto" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="foto-input" class="form-label">Seleccionar nueva foto</label>
                        <input type="file" class="form-control" id="foto-input" name="foto_perfil" accept="image/*">
                        <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</small>
                    </div>
                    <div id="mensaje-foto" class="mb-3"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="form-foto" class="btn btn-primary">
                    <i class="fa fa-upload me-2"></i>Subir Foto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambiar Contraseña -->
<div class="modal fade" id="cambiarPasswordModal" tabindex="-1" aria-labelledby="cambiarPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cambiarPasswordModalLabel">Cambiar Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php echo $msg_password; ?>
                    
                    <div class="mb-3">
                        <label for="password_actual" class="form-label">Contraseña Actual</label>
                        <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_nueva" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="password_nueva" name="password_nueva" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirmar" class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="cambiar_password" class="btn btn-warning">Cambiar Contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Script para manejar subida de foto de perfil
document.getElementById('form-foto').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    const fotoInput = document.getElementById('foto-input');
    const mensajeDiv = document.getElementById('mensaje-foto');
    
    if (!fotoInput.files[0]) {
        mensajeDiv.innerHTML = '<div class="alert alert-warning">Por favor selecciona una imagen.</div>';
        return;
    }
    
    formData.append('foto_perfil', fotoInput.files[0]);
    
    // Mostrar mensaje de carga
    mensajeDiv.innerHTML = '<div class="alert alert-info">Subiendo foto...</div>';
    
    fetch('subir_foto_perfil.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mensajeDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
            // Actualizar las imágenes con timestamp para evitar caché
            const timestamp = new Date().getTime();
            const newSrc = '../mostrar_foto_usuario.php?tipo=colaborador&id=<?= $colaborador_id ?>&t=' + timestamp;
            
            document.getElementById('preview-foto').src = newSrc;
            document.getElementById('foto-perfil-principal').src = newSrc;
            
            // Limpiar el input
            fotoInput.value = '';
            
            // Ocultar mensaje después de 3 segundos
            setTimeout(() => {
                mensajeDiv.innerHTML = '';
            }, 3000);
        } else {
            mensajeDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mensajeDiv.innerHTML = '<div class="alert alert-danger">Error al subir la foto.</div>';
    });
});

// Preview de imagen antes de subir
document.getElementById('foto-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-foto').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Si hay mensaje de perfil, mostrar el modal de edición
<?php if (!empty($msg_perfil)): ?>
var editModal = new bootstrap.Modal(document.getElementById('editarPerfilModal'));
editModal.show();
<?php endif; ?>

// Si hay mensaje de contraseña, mostrar el modal de contraseña
<?php if (!empty($msg_password)): ?>
var passModal = new bootstrap.Modal(document.getElementById('cambiarPasswordModal'));
passModal.show();
<?php endif; ?>
</script>

<?php include('footer.php'); ?>
