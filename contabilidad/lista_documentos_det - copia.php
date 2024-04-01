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
$id_doc = $_POST['id_doc'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_ctb_doc,fecha,id_manu,detalle,id_tercero,tipo_doc FROM ctb_doc WHERE id_ctb_doc=$id_doc";
    $rs = $cmd->query($sql);
    $datosMov = $rs->fetch();
    $tipo_dato = $datosMov['tipo_doc'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT sum(debito) as debito, sum(credito) as credito FROM ctb_libaux WHERE id_ctb_doc=$id_doc GROUP BY id_ctb_doc";
    $rs = $cmd->query($sql);
    $sumaMov = $rs->fetch();
    $dif = $sumaMov['debito'] - $sumaMov['credito'];
    $tipo_dato = $datosMov['tipo_doc'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$fecha = date('Y-m-d', strtotime($datosMov['fecha']));
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
                                    DETALLE DEL MOVIMIENTO CONTABLE
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                                <div class="right-block">
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small">NUMERO:</label></div>
                                        </div>
                                        <div class="col-10"><?php echo $datosMov['id_manu']; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small">FECHA:</label></div>
                                        </div>
                                        <div class="col-10"><?php echo $fecha; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small">TERCERO:</label></div>
                                        </div>
                                        <div class="col-10"><?php echo $datosMov['id_tercero']; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small">OBJETO:</label></div>
                                        </div>
                                        <div class="col-10"><?php echo $datosMov['detalle']; ?></div>
                                    </div>
                                    <?php
                                    if ($tipo_dato=='NCXP'){
                                    echo '
                                    <div class="row pb-2">
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small">CENTROS DE COSTOS:</label></div>
                                        </div>
                                        <div class="col-10"><a class="btn btn-outline-warning btn-sm btn-circle shadow-gb"><span class="fas fa-eye fa-lg"></span></a></div>
                                    </div>
                                    <div class="row pb-2" >
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small">VALOR:</label></div>
                                        </div>
                                        <div class="col-2"><input type="text" name="valor" id="valor" class="form-control form-control-sm" style="text-align: right;" required onkeyup="valorMiles(id)"></div>
                                        <div class="col-8"><a class="btn btn-outline-success btn-sm btn-circle shadow-gb"><span class="fas fa-plus fa-lg"></span></a></div>
                                    </div>
                                    <div class="row pb-2">
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small">DESCUENTOS:</label></div>
                                        </div>
                                        <div class="col-2"><input type="text" name="descuentos" id="descuentos" class="form-control form-control-sm" style="text-align: right;" required onkeyup="valorMiles(id)"></div>
                                        <div class="col-8"><a class="btn btn-outline-primary btn-sm btn-circle shadow-gb"><span class="fas fa-minus fa-lg"></span></a></div>
                                    </div>
                                    <div class="row pb-2" >
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small"></label></div>
                                        </div>
                                        <div class="col-10"> <button type="submit" class="btn btn-primary btn-sm" id="procesarMvtoDetalle">Procesar</button></div>
                                    </div>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <br>
                            <table id="tableMvtoContableDetalle" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 55%;">Cuenta</th>
                                        <th style="width: 15%;">Debito</th>
                                        <th style="width: 15%;">Credito</th>
                                        <th style="width: 15%;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="modificartableMvtoContableDetalle">

                                </tbody>
                                <input type="hidden" id="peReg" value="<?php echo $permisos['registrar']; ?>">
                                <!-- Formulario para nuevo reistro -->
                                <form id="formAddDetalleCtb">
                                    <tr>
                                        <th>
                                            <input type="hidden" id="id_ctb_doc" name="id_ctb_doc" value="<?php echo $_POST['id_doc']; ?>">
                                            <input type="text" name="codigoCta" id="codigoCta" class="form-control form-control-sm" value="" required>
                                            <input type="hidden" name="id_codigoCta" id="id_codigoCta" class="form-control form-control-sm" value="">
                                        </th>
                                        <th>
                                            <input type="text" name="valorDebito" id="valorDebito" class="form-control form-control-sm" size="6" value="" style="text-align: right;" required ondblclick="sumasIguales()" onkeyup="valorMiles(id)">
                                        </th>
                                        <th>
                                            <input type="text" name="valorCredito" id="valorCredito" class="form-control form-control-sm" size="6" value="" style="text-align: right;" required ondblclick="sumasIguales()" onkeyup="valorMiles(id)">
                                        </th>
                                        <th class="text-center">
                                            <button type="submit" class="btn btn-primary btn-sm" id="registrarMvtoDetalle">Agregar</button>
                                        </th>
                                    </tr>
                                </form>
                        </div>
                        <!-- Fin formulario -->
                        <tfoot>
                            <tr>
                                <th>Sumas iguales</th>
                                <th>
                                    <div class="text-right"><?php echo number_format($sumaMov['debito'], 2, '.', ','); ?></div>
                                </th>
                                <th>
                                    <div class="text-right"><?php echo number_format($sumaMov['credito'], 2, '.', ','); ?></div>
                                </th>
                                <th>
                                    <div class="text-right"></div>
                                </th>
                            </tr>
                        </tfoot>
                        <input type="hidden" id="valor_dif" name="valor_dif" value="<?php echo $dif; ?>">
                        </table>
                        <div class="text-center pt-4">

                            <a onclick="terminarDetalle('<?php echo $tipo_dato; ?>')" class="btn btn-danger" style="width: 7rem;" href="#"> Terminar</a>
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