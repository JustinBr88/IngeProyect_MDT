// Funcionalidad para el botón QR usando Google Charts API
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-qr').forEach(btn => {
        btn.addEventListener('click', function() {
            const tr = btn.closest('tr');
            // Obtén todos los datos del equipo de las celdas
            const equipo = {
                nombre: tr.children[1].textContent,
                categoria: tr.children[2].textContent,
                marca: tr.children[3].textContent,
                modelo: tr.children[4].textContent,
                serie: tr.children[5].textContent,
                costo: tr.children[6].textContent,
                ingreso: tr.children[7].textContent,
                depreciacion: tr.children[8].textContent,
            };
            
            // Texto más corto para el QR
            let qrText = `${equipo.nombre} | ${equipo.marca} ${equipo.modelo} | ${equipo.serie}`;
            
            // Texto completo para mostrar
            let textoCompleto = `Equipo: ${equipo.nombre}
Categoría: ${equipo.categoria}
Marca: ${equipo.marca}
Modelo: ${equipo.modelo}
Serie: ${equipo.serie}
Costo: ${equipo.costo}
Ingreso: ${equipo.ingreso}
Depreciación: ${equipo.depreciacion}`;
            
            // Genera QR usando Google Charts API
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(qrText)}`;
            
            // Muestra el QR
            document.getElementById('qrContainer').innerHTML = `<img src="${qrUrl}" alt="Código QR" class="img-fluid">`;
            
            // Muestra los datos completos del equipo
            document.getElementById('qrEquipoDatos').innerHTML = textoCompleto.replace(/\n/g, '<br>');
            
            // Muestra el modal
            const modal = new bootstrap.Modal(document.getElementById('modalQr'));
            modal.show();
        });
    });
});