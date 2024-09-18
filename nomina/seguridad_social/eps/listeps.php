<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../../head.php' ?>

<body class="sb-nav-fixed 
<?php if ($_SESSION['navarlat'] == '1') {
    echo 'sb-sidenav-toggled';
} ?>">
    <?php include '../../../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../../../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-building fa-lg" style="color: #1D80F7;"></i>
                                    LISTA DE ENTIDADES PROMOTORAS DE SALUD (EPS).
                                </div>
                                <?php if ((intval($permisos['registrar'])) === 1) {
                                    echo '<input type="hidden" id="peReg" value="1">';
                                } else {
                                    echo '<input type="hidden" id="peReg" value="0">';
                                } ?>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <table id="tableEmpEPSs" class="table table-striped table-bordered table-sm" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="text-center">Nombre</th>
                                        <th class="text-center">NIT</th>
                                        <th class="text-center">Teléfono</th>
                                        <th class="text-center">Correo</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="modificarEmpEPSs">
                                    </tbody>
                                </table>
                        </div>
                    </div>

                </div>
            </main>
            <?php include '../../../footer.php' ?>
        </div>
        <?php include '../../../modales.php' ?>
        <input type="text" id="delrow" value="0" hidden>
    </div>
    <?php include '../../../scripts.php' ?>
</body>

</html>