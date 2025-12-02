<!-- Footer Start -->
<footer class="bg-dark text-light mt-5 py-4">
  <div class="container text-center">
    <p class="mb-2">
      <a href="Home.php" class="text-light mr-2">Inicio</a> |
      <a href="Inventario.php" class="text-light mr-2">Inventario</a> |
      <a href="Categorias.php" class="text-light mr-2">Categorías</a> |
      <a href="Usuarios.php" class="text-light">Usuarios</a>
    </p>
    <p class="mb-0">&copy; <?php echo date("Y"); ?> MD Tecnología. Todos los derechos reservados.</p>
  </div>
</footer>
<!-- Footer End -->
<!-- Scripts generales -->
<!-- Bootstrap JS: prefer local copy first, fallback to CDN if local not available -->
<script>
  (function(){
    var localPaths = [
      '../js/vendor/bootstrap.bundle.min.js',
      'vendor/bootstrap/bootstrap.bundle.min.js'
    ];
    function tryLoad(index){
      if (index >= localPaths.length) {
        // no local file found, load CDN
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js';
        s.integrity = '';
        s.crossOrigin = 'anonymous';
        document.head.appendChild(s);
        console.info('Bootstrap local no encontrado, cargando CDN');
        return;
      }
      var path = localPaths[index];
      var s = document.createElement('script');
      s.src = path;
      s.onload = function(){ console.info('Bootstrap cargado desde local:', path); };
      s.onerror = function(){
        console.warn('Bootstrap local no encontrado en', path);
        tryLoad(index+1);
      };
      document.head.appendChild(s);
    }
    tryLoad(0);
  })();
</script>
<script src="../js/colaboradores.js"></script>
<!-- Scripts específicos de Inventario/Lotes -->
<script src="../js/lotes.js"></script>
<script src="../js/botonEditar_Guardar.js"></script>
<!-- Puedes incluir aquí otros scripts globales -->
</body>
</html>