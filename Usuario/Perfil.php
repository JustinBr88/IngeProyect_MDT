<?php
session_start();
include '../navbar_unificado.php';
require_once '../conexion.php';

// Verificar sesión
if (!isset($_SESSION['id']) || !isset($_SESSION['logeado'])) {
    header("Location: ../Login.php");
    exit();
}

$usuario_id = $_SESSION['id'];

try {
    $conexion = new Conexion();
    $mysqli = $conexion->getConexion();
    
    // Obtener datos del usuario
    $stmt = $mysqli->prepare("SELECT nombre, correo, rol, fecha_creacion FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    
    if (!$usuario) {
        header("Location: ../Login.php");
        exit();
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-4">
            <!-- Tarjeta de foto de perfil -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa fa-camera"></i> Foto de Perfil</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="../mostrar_foto_usuario.php" alt="Foto de perfil" 
                             class="rounded-circle img-thumbnail" 
                             style="width: 150px; height: 150px; object-fit: cover;" 
                             id="preview-foto">
                    </div>
                    <form id="form-foto-perfil" enctype="multipart/form-data">
                        <div class="mb-3">
                            <input type="file" class="form-control" id="foto_perfil" name="foto_perfil" 
                                   accept="image/*" required>
                            <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Máximo 5MB.</small>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-upload"></i> Subir Foto
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- Información del usuario -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fa fa-user"></i> Información Personal</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>Nombre:</strong></label>
                                <p class="form-control-static"><?= htmlspecialchars($usuario['nombre']) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>Correo Electrónico:</strong></label>
                                <p class="form-control-static"><?= htmlspecialchars($usuario['correo']) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>Rol:</strong></label>
                                <p class="form-control-static">
                                    <span class="badge bg-<?= $usuario['rol'] === 'admin' ? 'danger' : 'success' ?>">
                                        <?= $usuario['rol'] === 'admin' ? 'Administrador' : 'Colaborador' ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>Fecha de Registro:</strong></label>
                                <p class="form-control-static"><?= date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Cambiar contraseña -->
            <div class="card mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fa fa-lock"></i> Cambiar Contraseña</h5>
                </div>
                <div class="card-body">
                    <form id="form-cambiar-password">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_actual" class="form-label">Contraseña Actual</label>
                                    <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_nueva" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password_nueva" name="password_nueva" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmar" class="form-label">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" required>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="fa fa-save"></i> Cambiar Contraseña
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="mensaje-confirmacion">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Preview de la imagen antes de subirla
document.getElementById('foto_perfil').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-foto').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Subir foto de perfil
document.getElementById('form-foto-perfil').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('subir_foto_perfil.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recargar la imagen del navbar
            location.reload();
        }
        mostrarMensaje(data.message);
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje('Error al subir la foto');
    });
});

// Cambiar contraseña
document.getElementById('form-cambiar-password').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const passwordNueva = document.getElementById('password_nueva').value;
    const passwordConfirmar = document.getElementById('password_confirmar').value;
    
    if (passwordNueva !== passwordConfirmar) {
        mostrarMensaje('Las nuevas contraseñas no coinciden');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('cambiar_password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        mostrarMensaje(data.message);
        if (data.success) {
            this.reset();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje('Error al cambiar la contraseña');
    });
});

function mostrarMensaje(mensaje) {
    document.getElementById('mensaje-confirmacion').textContent = mensaje;
    new bootstrap.Modal(document.getElementById('modalConfirmacion')).show();
}
</script>

<?php include 'footer.php'; ?>
