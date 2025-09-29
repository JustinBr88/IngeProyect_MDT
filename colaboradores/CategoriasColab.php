<?php include('loginSesionColaborador.php'); ?>
<?php include('../navbar_unificado.php'); ?>
<div class="container mt-5">
    <h2>Categorías</h2>
    <!-- Formulario de Alta -->
    <form action="Categorias.php" method="post" class="mb-4">
        <div class="row">
            <div class="col-md-5">
                <label>Nombre de la categoría</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="col-md-7">
                <label>Descripción</label>
                <input type="text" name="descripcion" class="form-control">
            </div>
        </div>
        <div class="mt-3">
            <button class="btn btn-primary" type="submit" name="crear">Agregar categoría</button>
        </div>
    </form>
    <!-- Tabla de Categorías -->
    <table class="table table-bordered table-striped mt-4">
        <thead class="thead-dark">
            <tr>
                <th>ID</th><th>Nombre</th><th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            <?php
            require_once('conexion.php');
            $conexion = new Conexion(); 
            $result = $conexion->obtenerCategorias();
            foreach ($result as $row) {
                echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['nombre']}</td>
                    <td>{$row['descripcion']}</td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<?php include('footer.php'); ?>