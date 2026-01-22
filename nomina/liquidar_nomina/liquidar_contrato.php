<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
include '../../conexion.php';
include '../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
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
                                <div class="col-md-11">
                                    <i class="fas fa-copy fa-lg" style="color:#1D80F7"></i>
                                    LIQUIDACIÓN DE CONTRATO
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <form id="form_liq_contrato">
                                <table id="tableLiqContrato" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Elegir</th>
                                            <th>No. Contrato</th>
                                            <th>No. Documento</th>
                                            <th>Nombre Completo</th>
                                            <th>Fecha Inicio</th>
                                            <th>Fecha Termina</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modificarLiqContratos">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Elegir</th>
                                            <th>No. Contrato</th>
                                            <th>No. Documento</th>
                                            <th>Nombre Completo</th>
                                            <th>Fecha Inicio</th>
                                            <th>Fecha Termina</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </form>
                            <div class="center-block">
                                <div class="form-group">
                                    <a type="button" class="btn btn-secondary" href="javascript:history.back()"> Regresar</a>
                                    <a type="button" class="btn btn-secondary " href="../../inicio.php"> Cancelar</a>
                                    <a type="button" class="btn btn-success" id="btnLiqContratos"> Liquidar Contrato(s)</a>
                                </div>
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
</body>

</html>