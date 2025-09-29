<?php 
include 'loginSesion.php';
include('../navbar_unificado.php'); 
?>
      
    <h1>Bienvenido Usuario al sistema CMDB de MDT</h1>

    <!-- Noticias Start -->
    <div class="container-fluid pt-5">
      <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4">
        <span class="bg-secondary pr-3">Noticias Relevantes</span>
      </h2>
      <div class="row px-xl-5">
        <!-- Noticia 1: Hardware -->
        <div class="col-lg-12 mb-4">
          <div class="d-flex bg-light p-3 align-items-center">
            <img
              src="https://hardzone.es/app/uploads-hardzone.es/2025/07/chuwi-apertura.jpg?quality=80"
              alt="Noticia Hardware"
              class="img-fluid"
              style="width: 300px; height: 260px; margin-right: 20px"
            />
            <div>
              <h4 class="text-dark">
                Intel Presenta los Nuevos Procesadores Core Ultra para Empresas
              </h4>
              <p class="text-muted">
                Los nuevos procesadores Intel Core Ultra de 15ª generación ofrecen un rendimiento excepcional para workstations empresariales, con tecnología de IA integrada que optimiza el inventario de hardware. Ideal para sistemas CMDB que requieren procesamiento de grandes volúmenes de datos de activos tecnológicos en tiempo real.
              </p>
              <a
                href=""
                class="text-primary"
                >Leer más</a
              >
            </div>
          </div>
        </div>
        <!-- Noticia 2: Software -->
        <div class="col-lg-12 mb-4">
          <div class="d-flex bg-light p-3 align-items-center">
            <img
              src="https://uhf.microsoft.com/images/microsoft/RE1Mu3b.png"
              alt="Noticia Software"
              class="img-fluid"
              style="width: 300px; height: 260px; margin-right: 20px"
            />
            <div>
              <h4 class="text-dark">
                Microsoft 365 Copilot Revoluciona la Gestión de Activos de Software
              </h4>
              <p class="text-muted">
                La integración de inteligencia artificial en Microsoft 365 Copilot está transformando la manera en que las empresas gestionan sus licencias de software y activos digitales. Esta herramienta facilita el seguimiento automatizado de versiones, licencias y actualizaciones en sistemas CMDB empresariales.
              </p>
              <a
                href="https://www.microsoft.com/en-us/microsoft-365/business/copilot-for-microsoft-365"
                class="text-primary"
                >Leer más</a
              >
            </div>
          </div>
        </div>
        <!-- Noticia 3: Importancia del CMDB -->
        <div class="col-lg-12 mb-4">
          <div class="d-flex bg-light p-3 align-items-center">
            <!-- Contenedor del video, ajustado al mismo tamaño que las imágenes -->
            <div class="embed-responsive embed-responsive-16by9">
              <iframe
                title="noticia3-youtube"
                class="embed-responsive-item"
                src="https://youtu.be/SmkDpuN17hw?si=vXDmyFIb2KiaF_WF"
                allow="fullscreen"
                style="
                  width: 300px;
                  height: 260px;
                  margin-right: 20px;
                  border-radius: 5px;
                  object-fit: cover;
                "
              >
              </iframe>
            </div>
            <!-- Contenido del video -->
            <div>
              <h4 class="text-dark">La Importancia Crítica de un Sistema de Inventario CMDB</h4>
              <p class="text-muted">
                Un Sistema de Gestión de Base de Datos de Configuración (CMDB) es esencial para empresas modernas. Permite el control total de activos tecnológicos, reduce costos operativos, mejora la seguridad y facilita la toma de decisiones estratégicas basadas en datos precisos del inventario empresarial.
              </p>
              <a
                href="https://www.servicenow.com/products/it-service-management/what-is-cmdb.html"
                class="text-primary"
                >Conocer más sobre CMDB</a
              >
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Noticias End -->

<?php include('footer.php'); ?>

    <!-- JavaScript  -->
    <script src="../js/home.js"></script>
  </body>
</html>