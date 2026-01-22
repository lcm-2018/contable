<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$id_retroactivo = isset($_POST['id_retroactivo']) ? $_POST['id_retroactivo'] : exit('Acceso no permitido a este archivo');
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../../head.php' ?>

<body class="sb-nav-fixed <?php
                            if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            }
                            ?>">
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
                                    <i class="fas fa-list-alt fa-lg" style="color:#1D80F7"></i>
                                    LISTA DE EMPLEADOS PARA LIQUIDAR RETROACTIVOS.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <form id="formListaEmpleadosRetroactivo">
                                <input type="hidden" id="id_retroactivo" name="id_retroactivo" value="<?php echo $id_retroactivo ?>">
                                <div class="text-right pb-3">
                                    <button type="button" class="btn btn-sm btn-primary" id="btnLiquidarRetroactivo">Liquidar retroactivo</button>
                                    <a type="button" class="btn btn-secondary btn-sm" href="lista_retroactivos.php">Regresar</a>
                                </div>
                                <table id="tableEmpleadosRetroactivo" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr class="text-center">
                                            <th>
                                                <div class="text-center"><input type="checkbox" id="selectAll" class="check" title="Desmarcar todos" checked></div>
                                            </th>
                                            <th>No Doc.</th>
                                            <th>Nombre Completo</th>
                                            <th>Estado</th>
                                            <th>Sindicato</th>
                                        </tr>
                                    </thead>
                                    </tbody>
                                </table>
                            </form>
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