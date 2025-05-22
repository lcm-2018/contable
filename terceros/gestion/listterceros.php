<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../../conexion.php';
include '../../permisos.php';
$key = array_search('52', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$vigencia = $_SESSION['vigencia'];

?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php' ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
    <?php include '../../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-10">
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    LISTA DE TERCEROS
                                </div>
                                <?php if (PermisosUsuario($permisos, 5201, 2) || $id_rol == 1) {
                                    echo '<input type="hidden" id="peReg" value="1">';
                                } else {
                                    echo '<input type="hidden" id="peReg" value="0">';
                                }
                                ?>
                                <div class="col-md-2 text-right">
                                    <button class="btn btn-warning btn-sm" id="btnActualizaRepositorio" title="Actualizar repositorio de terceros">
                                        <span class="mr-2"></span><i class="fas fa-user-edit fa-lg"></i>
                                    </button>
                                    <!-- botón para descargar excel -->
                                    <button class="btn btn-info btn-sm" id="btnReporteTerceros" title="Descargar Informe de Terceros">
                                        <i class="fas fa-file-excel fa-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">


                            <!--Opciones de filtros -->
                            <div class="form-row">
                                <div class="form-group col-md-2">
                                    <input type="text" class="filtro form-control form-control-sm" id="txt_ccnit_filtro" placeholder="Doc / Nit">
                                </div>
                                <div class="form-group col-md-3">
                                    <input type="text" class="filtro form-control form-control-sm" id="txt_tercero_filtro" placeholder="Tercero">
                                </div>
                                <div class="form-group col-md-1">
                                    <a type="button" id="btn_buscar_filtro" class="btn btn-outline-success btn-sm" title="Filtrar">
                                        <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                                    </a>
                                </div>

                                <!-- para dashboard 
                                <div class="form-group col-md-1">
                                    <a type="button" id="btn_iniciar_dashboard" class="btn btn-outline-success btn-sm" title="Dashboard">
                                        <span class="fas fa-chart-line fa-lg" aria-hidden="true"></span>
                                    </a>
                                </div>
                                <div class="form-group col-md-1">
                                    <a type="button" id="btn_dashboard" class="btn btn-outline-primary btn-sm" title="Dashboard">
                                        <span class="fas fa-chart-line fa-lg" aria-hidden="true"></span>
                                    </a>
                                </div>
                                <div class="form-group col-md-1">
                                    <a type="button" id="btn_detener_dashboard" class="btn btn-outline-danger btn-sm" title="Dashboard">
                                        <span class="fas fa-chart-line fa-lg" aria-hidden="true"></span>
                                    </a>
                                </div> -->
                            </div>

                            <div class="table-responsive">
                                <table id="tableTerceros" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>No. Doc.</th>
                                            <th>Nombre / Razón Social</th>
                                            <th>Tipo</th>
                                            <th>Ciudad</th>
                                            <th>Dirección</th>
                                            <th>Teléfono</th>
                                            <th>Correo</th>
                                            <th>Estado</th>
                                            <th>Acción</th>

                                        </tr>
                                    </thead>
                                    <tbody id="modificarTerceros">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>No. Doc.</th>
                                            <th>Nombre / Razón Social</th>
                                            <th>Tipo</th>
                                            <th>Ciudad</th>
                                            <th>Dirección</th>
                                            <th>Teléfono</th>
                                            <th>Correo</th>
                                            <th>Estado</th>
                                            <th>Acción</th>

                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../footer.php' ?>
        </div>
        <?php include '../../modales.php' ?>
    </div>
    <?php include '../../scripts.php' ?>
    <script type="text/javascript" src="../../terceros/js/historialtercero/historialtercero.js?v=<?php echo date('YmdHis') ?>"></script>
</body>

</html>