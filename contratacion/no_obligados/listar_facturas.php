<?php
session_start();
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
                                    <i class="fas fa-ticket-alt fa-lg" style="color:#1D80F7"></i>
                                    LISTA DE FACTURAS DE ADQUISICIONES CON NO OBLIGADOS.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <input type="hidden" id="peReg" value="<?php echo $permisos['registrar'] ?>">
                            <table id="tableFacurasNoObligados" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                <thead>
                                    <tr class="text-center">
                                        <th>ID</th>
                                        <th>Fecha Compra</th>
                                        <th>Fecha Vencimimiento</th>
                                        <th>Método Pago</th>
                                        <th>Forma Pago</th>
                                        <th>Tipo Documento</th>
                                        <th>No. Documento</th>
                                        <th>Nombre y/o Razón social</th>
                                        <th>Detalles</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="modificarFacturaNoObligados">
                                </tbody>
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