document.addEventListener('DOMContentLoaded', function() {
  // Confirmación para editar perfil
  var form = document.getElementById('formEditarPerfil');
  if (form) {
    form.addEventListener('submit', function(e) {
      if (!confirm('¿Estás seguro de que deseas guardar los cambios en tu perfil?')) {
        e.preventDefault();
      }
    });
  }
   // Confirmación para agregar colaborador
  document.addEventListener('DOMContentLoaded', function() {
  var formAgregar = document.getElementById('formAgregarColaborador');
  if (formAgregar) {
    formAgregar.addEventListener('submit', function(e) {
      if (!confirm('¿Confirma que desea agregar este colaborador con los datos ingresados?')) {
        e.preventDefault();
      }
    });
  }
});

  // Confirmación para dar de baja colaborador
  var bajaForms = document.querySelectorAll('.formBajaColaborador');
  bajaForms.forEach(function(f) {
    f.addEventListener('submit', function(e) {
      if (!confirm('¿Realmente deseas dar de baja a este colaborador? Esta acción no se puede deshacer.')) {
        e.preventDefault();
      }
    });
  });
});

// Confirmación para cerrar sesión colaborador
var btnLogout = document.getElementById('btnLogoutColaborador');
if (btnLogout) {
  btnLogout.addEventListener('click', function(e) {
    if (!confirm('¿Está seguro que desea cerrar sesión?')) {
      e.preventDefault();
    }
  });
}