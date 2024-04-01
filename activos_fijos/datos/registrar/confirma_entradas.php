<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$id_c = isset($_POST['ids']) ? $_POST['ids'] : exit('Acción no permitida');
//API URL
$url = $api . 'terceros/datos/res/lista/compra_entregado/' . $id_c;
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$compra_entregada = json_decode($result, true);
$adq = explode('|', $id_c);
$idTerApi = $adq[0];
$datas = $adq[2];
$id_entrada = $adq[4];
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
                                    <i class="fas fa-list-alt fa-lg" style="color:#1D80F7"></i>
                                    ENTRADAS PENDIENTES ACTIVOS FIJOS.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="text-right mb-3">
                                <a type="button" class="btn btn-secondary  btn-sm" href="../../entradas_activos_fijos.php">Regresar</a>
                            </div>
                            <input type="hidden" id="peReg" value="<?php echo $permisos['registrar'] ?>">
                            <form id="formEncabEntraActFijo">
                                <input type="hidden" id="idsconfentrada" value="<?php echo $id_c ?>">
                                <input type="hidden" name="id_contrato" id="id_contrato" value="<?php echo $compra_entregada['id_c'] ?>">
                                <input type="hidden" name="id_entrada" id="id_entrada" value="<?php echo $id_entrada ?>">
                                <input type="hidden" name="idadq" id="idadq" value="<?php echo $datas ?>">
                                <input type="hidden" name="idTerApi" id="idTerApi" value="<?php echo $idTerApi ?>">
                            </form>
                            <table id="tableEntradasActivoFijos" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>ID api</th>
                                        <th>Bien o servicio</th>
                                        <th>Entrega Actual</th>
                                        <th>Precio</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="modificarConfEntradasActFijos">
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