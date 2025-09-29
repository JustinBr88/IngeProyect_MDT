document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const usuario = document.getElementById('username').value;
    const contrasena = document.getElementById('password').value;

    const formData = new FormData();
    formData.append('usuario', usuario);
    formData.append('contrasena', contrasena);

    try {
        // Usar URL relativa para evitar problemas CORS
        const url = '../validar_login.php';
        console.log('Enviando validación de login a:', url);

        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const text = await response.text();
        console.log('Respuesta cruda:', text);
        
        // Verificar si la respuesta es JSON válida
        let result;
        try {
            result = JSON.parse(text);
        } catch (parseError) {
            console.error('Error parsing JSON:', parseError);
            console.error('Respuesta recibida:', text);
            alert('Error: Respuesta del servidor no es JSON válida');
            return;
        }

        if (result.success) {
            alert(result.mensaje);
            // Redirigir según el tipo de usuario
            if (result.redirect) {
                window.location.href = `../${result.redirect}`;
            } else {
                // Fallback por tipo
                if (result.tipo === 'colaborador') {
                    window.location.href = '../colaboradores/portal_colaborador.php';
                } else {
                    window.location.href = 'Home.php';
                }
            }
        } else {
            alert(result.mensaje);
        }
    } catch (error) {
        console.error('Error en login:', error);
        console.error('Tipo de error:', error.name);
        console.error('Mensaje:', error.message);
        
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            alert('Error de conexión: No se puede conectar al servidor. Verifica que el servidor esté ejecutándose.');
        } else {
            alert(`Error de conexión: ${error.message}`);
        }
    }
});