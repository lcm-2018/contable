<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php';
// Consulta tipo de presupuesto
$id_pto_presupuestos = $_POST['id_pto'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $sql = "SELECT nombre FROM pto_presupuestos WHERE id_pto_presupuestos=$id_pto_presupuestos";
    $rs = $cmd->query($sql);
    $nomPresupuestos = $rs->fetch();

    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
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
                                    LISTADO DE COMPROMISOS PARA CUENTAS POR PAGAR
                                </div>
                                <input type="hidden" id="id_pto_ppto" value="<?php echo $_POST['id_pto']; ?>">
                                <input type="hidden" id="id_ctb_doc" value="NCXP">
                                <input type="hidden" id="peReg" value="<?php echo $permisos['registrar']; ?>">

                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                               
                                <table id="tableEjecPresupuestoCxp" class="table table-striped table-bordered table-sm table-hover shadow align-middle" style="table-layout: fixed;width: 98%;">
                                    <thead>
                                        <tr>
                                            <th style="width: 8%;">Numero</th>
                                            <th style="width: 8%;">Cdp</th>
                                            <th style="width: 8%;">Fecha</th>
                                            <th style="width: 44%;">Tercero</th>
                                            <th style="width: 12%;">Valor</th>
                                            <th style="width: 10%;">Causación</th>
                                            <th style="width: 10%;">Acciones</th>

                                        </tr>
                                    </thead>
                                    <tbody id="modificarEjecPresupuestoCxp">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Numero</th>
                                            <th>Cdp</th>
                                            <th>Fecha</th>
                                            <th>Tercero</th>
                                            <th>Valor</th>
                                            <th>Causación</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="text-center pt-4">
                                <a type="button" class="btn btn-danger" style="width: 7rem;" onclick="cambiaListadoContable('NCXP')"> VOLVER</a>
                            </div>
                        </div>

                    </div>
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