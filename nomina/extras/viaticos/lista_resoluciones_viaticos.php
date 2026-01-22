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

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
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
                                    <i class="fas fa-clipboard-list fa-lg" style="color:#1D80F7"></i>
                                    LISTADO DE RESOLUCIONES PARA VIÁTICOS.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="row">
                                <div class="input-group mb-3 col-md-3 offset-md-9">
                                    <input type="number" class="form-control form-control-sm" placeholder="Grupo de resoluciones" id="numGrupoResols">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-primary btn-sm" type="button" id="btnAWordxGrupo" title="Generar Resoluciones por grupo">
                                            <i class="fas fa-file-word"></i>
                                            Por Grupo
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php
                            if (PermisosUsuario($permisos, 5103, 2) || $id_rol == 1) {
                                echo '<input type="hidden" id="peReg" value="1">';
                            } else {
                                echo '<input type="hidden" id="peReg" value="0">';
                            }
                            ?>
                            <table id="tableListaResolucionesViaticos" class="table table-striped table-bordered table-sm nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="text-center">Grupo</th>
                                        <th class="text-center">Número<br>Resolución</th>
                                        <th class="text-center">CDP</th>
                                        <th class="text-center">Número<br>Documento</th>
                                        <th class="text-center">Nombre Completo</th>
                                        <th class="text-center">Fecha<br>Inicia</th>
                                        <th class="text-center">Fecha<br>Termina</th>
                                        <th class="text-center">Total<br>Días</th>
                                        <th class="text-center">Días<br>Pernocta</th>
                                        <th class="text-center">Objetivo</th>
                                        <th class="text-center">Destino</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="modificarResolucionViatics">
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </main>
            <?php include '../../../footer.php' ?>
        </div>
        <?php include '../../../modales.php' ?>
    </div>
    <?php include '../../../scripts.php' ?>
</body>

</html>