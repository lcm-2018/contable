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
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    LISTA DE EMPLEADOS
                                </div>
                                <?php
                                if ((PermisosUsuario($permisos, 5101, 2) || $id_rol == 1)) {
                                    echo '<input type="hidden" id="peReg" value="1">';
                                } else {
                                    echo '<input type="hidden" id="peReg" value="0">';
                                } ?>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                                <?php
                                if ((PermisosUsuario($permisos, 5101, 1) || $id_rol == 1)) {
                                    ?>
                                    <table id="tableListEmpleados"
                                        class="table table-striped table-bordered table-sm nowrap table-hover shadow"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>No. Doc.</th>
                                                <th>Nombre</th>
                                                <th>Correo</th>
                                                <th>Teléfono</th>
                                                <th>Salario</th>
                                                <th>Estado</th>
                                                <th>Acción</th>

                                            </tr>
                                        </thead>
                                        <tbody id="modificarEmpleados">
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th>ID</th>
                                                <th>No. Doc.</th>
                                                <th>Nombre</th>
                                                <th>Correo</th>
                                                <th>Teléfono</th>
                                                <th>Salario</th>
                                                <th>Estado</th>
                                                <th>Opciones</th>

                                            </tr>
                                        </tfoot>
                                    </table>
                                    <?php
                                } ?>
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