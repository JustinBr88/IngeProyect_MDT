<?php 
include 'loginSesion.php';
include('../navbar_unificado.php'); 
require_once '../conexion.php';

try {
    $conexion = new Conexion();

    // Obtener estadísticas por categoría
    $estadisticas = $conexion->obtenerEstadisticasPorCategoria();
    $equiposDisponibles = $conexion->obtenerEquiposDisponiblesPorCategoria();
    $equiposAsignados = $conexion->obtenerEquiposAsignadosPorCategoria();
    
    // Verificar si hay datos
    if (empty($estadisticas)) {
        echo '<div class="container mt-5">
                <div class="alert alert-warning">
                    <h4>No hay datos para mostrar</h4>
                    <p>No se encontraron categorías o equipos en el inventario. Por favor, agrega categorías y equipos antes de generar reportes.</p>
                    <a href="Categorias.php" class="btn btn-primary">Gestionar Categorías</a>
                    <a href="Inventario.php" class="btn btn-success">Gestionar Inventario</a>
                </div>
              </div>';
        include('footer.php');
        exit;
    }
} catch (Exception $e) {
    echo '<div class="container mt-5">
            <div class="alert alert-danger">
                <h4>Error al cargar reportes</h4>
                <p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>
            </div>
          </div>';
    include('footer.php');
    exit;
}
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Reportes de Inventario</h2>
        <div>
            <button class="btn btn-success btn-reporte" onclick="exportarExcel('inventario')">
                <i class="fas fa-file-excel"></i> Excel Inventario
            </button>
            <button class="btn btn-primary btn-reporte ms-2" onclick="exportarExcel('asignaciones')">
                <i class="fas fa-file-excel"></i> Excel Asignaciones
            </button>
            <button class="btn btn-info btn-reporte ms-2" onclick="exportarExcel('estadisticas')">
                <i class="fas fa-chart-bar"></i> Estadísticas
            </button>
        </div>
    </div>

    <!-- Resumen General -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card reporte-card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-pie"></i> Resumen General de Inventario</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php 
                        $totalEquipos = 0;
                        $totalAsignados = 0;
                        foreach($estadisticas as $stat): 
                            $totalEquipos += $stat['total_equipos'];
                            $totalAsignados += $stat['equipos_asignados'];
                        endforeach; 
                        $totalDisponibles = $totalEquipos - $totalAsignados;
                        ?>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white estadistica-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-boxes fa-2x mb-2"></i>
                                    <h3><?= $totalEquipos ?></h3>
                                    <p>Total Equipos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white estadistica-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                                    <h3><?= $totalDisponibles ?></h3>
                                    <p>Disponibles</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white estadistica-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-check fa-2x mb-2"></i>
                                    <h3><?= $totalAsignados ?></h3>
                                    <p>Asignados</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white estadistica-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-list fa-2x mb-2"></i>
                                    <h3><?= count($estadisticas) ?></h3>
                                    <p>Categorías</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas por Categoría -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Equipos por Categoría</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="tablaReportes">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Categoría</th>
                                    <th>Total Equipos</th>
                                    <th>Disponibles</th>
                                    <th>Asignados</th>
                                    <th>% Utilización</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($estadisticas as $stat): 
                                    $porcentaje = $stat['total_equipos'] > 0 ? 
                                        round(($stat['equipos_asignados'] / $stat['total_equipos']) * 100, 1) : 0;
                                    $disponibles = $stat['total_equipos'] - $stat['equipos_asignados'];
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($stat['categoria']) ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?= $stat['total_equipos'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success"><?= $disponibles ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning"><?= $stat['equipos_asignados'] ?></span>
                                    </td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?= $porcentaje ?>%" 
                                                 aria-valuenow="<?= $porcentaje ?>" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                <?= $porcentaje ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" 
                                                onclick="verDetalle('<?= $stat['categoria_id'] ?>', '<?= htmlspecialchars($stat['categoria']) ?>')">
                                            Ver Detalle
                                        </button>
                                        <button class="btn btn-sm btn-success" 
                                                onclick="exportarCategoria('<?= $stat['categoria_id'] ?>', '<?= htmlspecialchars($stat['categoria']) ?>')">
                                            Excel
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros Adicionales -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Filtros de Reporte</h4>
                </div>
                <div class="card-body">
                    <form id="filtrosReporte">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Categoría:</label>
                                <select class="form-select" id="filtroCategoria">
                                    <option value="">Todas las categorías</option>
                                    <?php foreach($estadisticas as $stat): ?>
                                    <option value="<?= $stat['categoria_id'] ?>">
                                        <?= htmlspecialchars($stat['categoria']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Estado:</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos</option>
                                    <option value="disponible">Disponibles</option>
                                    <option value="asignado">Asignados</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Fecha desde:</label>
                                <input type="date" class="form-control" id="fechaDesde">
                            </div>
                            <div class="col-md-3">
                                <label>Fecha hasta:</label>
                                <input type="date" class="form-control" id="fechaHasta">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="button" class="btn btn-primary" onclick="aplicarFiltros()">
                                    Aplicar Filtros
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="limpiarFiltros()">
                                    Limpiar
                                </button>
                                <button type="button" class="btn btn-success" onclick="exportarFiltrado()">
                                    Exportar Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Detalle por Categoría -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Equipos - <span id="categoriaDetalle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="contenidoDetalle">
                    <!-- Aquí se cargará el detalle por AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script src="../js/reportes.js"></script>
