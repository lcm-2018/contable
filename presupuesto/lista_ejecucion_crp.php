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
// Consulta tipo de presupuesto
$id_pto_documento = $_POST['id_crp'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $sql = "SELECT id_pto_presupuestos,fecha, id_manu,objeto,id_auto FROM pto_documento WHERE id_pto_doc=$id_pto_documento";
    $rs = $cmd->query($sql);
    $datosCrp = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT sum(valor) as valorCrp FROM pto_documento_detalles WHERE id_pto_doc=$datosCrp[id_auto]";
    $rs = $cmd->query($sql);
    $totalCrp = $rs->fetch();
    // total con puntos de mailes number_format()
    $total = number_format($totalCrp['valorCrp'], 2, '.', ',');
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$fecha = date('Y-m-d', strtotime($datosCrp['fecha']));

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
                                    DETALLE CERTIFICADO DE REGISTRO PRESUPUESTAL
                                </div>

                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                                <div class="right-block">
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small">NUMERO CRP:</label></div>
                                        </div>
                                        <div class="col-10"><?php echo $datosCrp['id_manu']; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small">FECHA:</label></div>
                                        </div>
                                        <div class="col-10"><?php echo $fecha; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small">OBJETO:</label></div>
                                        </div>
                                        <div class="col-10"><?php echo $datosCrp['objeto']; ?></div>
                                    </div>

                                </div>
                            </div>
                            <br>
                            <table id="tableEjecCrp" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Valor</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="modificarEjecCrp">
                                </tbody>
                                <input type="hidden" id="id_pto_ppto" value="<?php echo $datosCrp['id_pto_presupuestos']; ?>">
                                <input type="hidden" id="id_pto_doc" value="<?php echo $id_pto_documento; ?>">
                                <input type="hidden" id="peReg" value="<?php echo $permisos['registrar']; ?>">

                                <tfoot>
                                    <tr>
                                        <th>Total</th>
                                        <th>
                                            <div class="text-right"><?php echo $total; ?></div>
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="text-center pt-4">
                                <a onclick="cambiaListado(2)" class="btn btn-danger" style="width: 7rem;" href="#"> VOLVER</a>

                            </div>
                        </div>

                    </div>
                </div>
                <div>

                </div>
            </main>
            <?php include '../footer.php' ?>
        </div>
        <!-- Modal formulario-->
        <div class="modal fade" id="divModalForms" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div id="divTamModalForms" class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body text-center" id="divForms">

                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php include '../scripts.php' ?>
</body>

</html>