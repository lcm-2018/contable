<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
$anio = $_SESSION['vigencia'];

include '../../conexion.php';
include '../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$corte = isset($_POST['corte']) ? $_POST['corte'] : NULL;
$carcater_empresa = $_SESSION['caracter'] == 2 ? $_SESSION['caracter'] : 1;
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php' ?>

<body class="sb-nav-fixed <?php
                            if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            }
                            ?>">
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
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    LIQUIDAR PRESTACIONES SOCIALES.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <input type="hidden" id="caracter_empresa" value="<?php echo $carcater_empresa ?>">
                            <div class="form-group col-md-3">
                                <label class="small">FECHA DE CORTE</label>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="date" class="form-control" id="datFecCorte" aria-describedby="btnFiltraEmpleados" value="<?php echo $corte ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-info" id="btnFiltraEmpleados">FILTRAR</button>
                                    </div>
                                </div>
                            </div>
                            <?php
                            if ($corte !== NULL) {
                            ?>
                                <form id="formLiqPreSoc">
                                    <table id="tableLiqPresSociales" class="table table-striped table-bordered table-sm nowrap" style="width:100%">
                                        <thead>
                                            <tr class="text-center centro-vertical">
                                                <th>
                                                    <div class="text-center">
                                                        <div class="form-check">
                                                            <input class="form-check-input position-static" type="checkbox" id="selectAll" checked>
                                                        </div>
                                                    </div>
                                                </th>
                                                <th>No. Doc.</th>
                                                <th>Nombre Completo</th>
                                                <th>Fecha termina</th>
                                                <th class="w-10">Dias Compensa</th>
                                            </tr>
                                        </thead>
                                        <tbody id="LiqPresSocial">
                                        </tbody>
                                    </table>
                                    <div class="text-center">
                                        <?php
                                        if (PermisosUsuario($permisos, 5107, 2) || $id_rol == 1) {
                                        ?>
                                            <button type="button" class="btn btn-outline-primary" id="liqPreSocial">Liquidar</button>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </form>
                            <?php
                            } ?>
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