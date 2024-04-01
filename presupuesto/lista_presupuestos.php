<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php';

?>


<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] === '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
    <?php include '../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    LISTADO DE PRESUPUESTO
                                </div>
                                <?php
                                if (PermisosUsuario($permisos, 5401, 0) || $id_rol == 1) {
                                    echo '<input type="hidden" id="peReg" value="1">';
                                } else {
                                    echo '<input type="hidden" id="peReg" value="0">';
                                } ?>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                                <table id="tablePresupuesto" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Presupuesto</th>
                                            <th>Tipo</th>
                                            <th>Vigencia</th>
                                            <th>Acciones</th>


                                        </tr>
                                    </thead>
                                    <tbody id="modificarPresupuesto">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>ID</th>
                                            <th>Presupuesto</th>
                                            <th>Tipo</th>
                                            <th>Vigencia</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../footer.php' ?>
        </div>
        <?php include '../modales.php' ?>
    </div>
    <?php include '../scripts.php' ?>
</body>

</html>