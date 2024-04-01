<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../../conexion.php';
include '../../permisos.php';
$key = array_search('53', array_column($perm_modulos, 'id_modulo'));
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
                                <div class="col-md-11">
                                    <i class="fas fa-copy fa-lg" style="color:#1D80F7"></i>
                                    LISTADO DE ADQUISICIONES
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <?php if (PermisosUsuario($permisos, 5302, 2) || $id_rol == 1) {
                                echo '<input type="hidden" id="peReg" value="1">';
                            } else {
                                echo '<input type="hidden" id="peReg" value="0">';
                            } ?>
                            <table id="tableAdquisiciones" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Modalidad</th>
                                        <!--<th>Adquicisión</th>-->
                                        <th>valor</th>
                                        <th>Fecha</th>
                                        <th>Objeto</th>
                                        <th>Tercero</th>
                                        <th>Estado</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="modificarAdquisiciones">
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>Modalidad</th>
                                        <th>Adquicisión</th>
                                        <th>Valor</th>
                                        <th>Fecha</th>
                                        <th>Tercero</th>
                                        <th>Objeto</th>
                                        <!--<th>Estado</th>-->
                                        <th>Acción</th>
                                    </tr>
                                </tfoot>
                            </table>
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