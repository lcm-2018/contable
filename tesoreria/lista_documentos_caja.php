<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../financiero/consultas.php';
include '../terceros.php';

// Consulta tipo de presupuesto
$id_doc_pag = isset($_POST['id_doc']) ? $_POST['id_doc'] : exit('Acceso no disponible');
$id_cop = isset($_POST['id_cop']) ? $_POST['id_cop'] : 0;
$tipo_dato = isset($_POST['tipo_dato']) ? $_POST['tipo_dato'] : 0;
$tipo_mov = isset($_POST['tipo_movi']) ? $_POST['tipo_movi'] : 0;
$tipo_var = isset($_POST['tipo_var']) ? $_POST['tipo_var'] : 0;
$id_arq = isset($_POST['id_arq']) ? $_POST['id_arq'] : 0;
$id_vigencia = $_SESSION['id_vigencia'];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

try {
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `ctb_doc`.`id_manu`
                , `ctb_fuente`.`nombre`
                , `ctb_doc`.`fecha`
                , `ctb_doc`.`detalle`
                , `ctb_doc`.`id_tercero`
                , `ctb_doc`.`estado`
                , `tes_caja_const`.`nombre_caja`
                , `tes_caja_const`.`fecha_ini`
                , `tes_caja_const`.`id_caja_const`
            FROM
                `ctb_doc`
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                INNER JOIN `tes_caja_doc` 
                    ON (`tes_caja_doc`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `tes_caja_const` 
                    ON (`tes_caja_doc`.`id_caja` = `tes_caja_const`.`id_caja_const`)    
            WHERE (`ctb_doc`.`id_ctb_doc` = $id_doc_pag)";
    $rs = $cmd->query($sql);
    $datosDoc = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_manu = $datosDoc['id_manu'];
if (!empty($datosDoc)) {
    $id_t = ['0' => $datosDoc['id_tercero']];
    $ids = implode(',', $id_t);
    $dat_ter = getTerceros($ids, $cmd);
    $tercero = ltrim($dat_ter[0]['nom_tercero']);
} else {
    $tercero = '---';
}
try {
    $sql = "SELECT
                `id_ctb_doc`
                , SUM(`valor`) AS `valor`
            FROM
                `tes_detalle_pago`
            WHERE (`id_ctb_doc` = $id_doc_pag)";
    $rs = $cmd->query($sql);
    $values = $rs->fetch();
    $valor_pago = !empty($values) ? $values['valor'] : 0;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `id_ctb_doc`
                , SUM(`valor`) AS `valor`
            FROM
                `tes_detalle_pago`
            WHERE (`id_ctb_doc` = $id_doc_pag)";
    $rs = $cmd->query($sql);
    $values = $rs->fetch();
    $valor_pago = !empty($values) ? $values['valor'] : 0;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $query = "SELECT SUM(`valor`) AS `val_imputacion` FROM `tes_caja_mvto` WHERE `id_ctb_doc` = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_doc_pag, PDO::PARAM_INT);
    $query->execute();
    $val_imp = $query->fetch(PDO::FETCH_ASSOC)['val_imputacion'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$ver = 'readonly';
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../head.php'; ?>

<body class="sb-nav-fixed <?php echo $_SESSION['navarlat'] === '1' ?  'sb-sidenav-toggled' : '' ?>">
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
                                    DETALLE DEL COMPROBANTE DE <b>CAJA MENOR</b>
                                </div>
                            </div>
                        </div>
                        <!-- Formulario para nuevo reistro -->
                        <?php
                        if (PermisosUsuario($permisos, 5601, 2) || $id_rol == 1) {
                            echo '<input type="hidden" id="peReg" value="1">';
                        } else {
                            echo '<input type="hidden" id="peReg" value="0">';
                        }
                        ?>
                        <form id="formAddDetallePag">
                            <div class="card-body" id="divCuerpoPag">
                                <div>
                                    <div class="right-block">
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <span class="small">NUMERO ACTO: </span>
                                            </div>
                                            <div class="col-10">
                                                <input type="number" name="numDoc" id="numDoc" class="form-control form-control-sm" value="<?php echo $id_manu; ?>" required readonly>
                                                <input type="hidden" id="tipodato" name="tipodato" value="<?php echo $tipo_dato; ?>">
                                                <input type="hidden" id="id_cop_pag" name="id_cop_pag" value="<?php echo $id_cop; ?>">
                                                <input type="hidden" id="id_arqueo" name="id_arqueo" value="<?php echo $id_arq; ?>">
                                            </div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <span class="small">FECHA:</span>
                                            </div>
                                            <div class="col-10">
                                                <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" value="<?php echo date('Y-m-d', strtotime($datosDoc['fecha'])); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <span class="small">TERCERO:</span>
                                            </div>
                                            <div class="col-10">
                                                <input type="text" name="tercero" id="tercero" class="form-control form-control-sm" value="<?php echo $tercero; ?>" required readonly>
                                                <input type="hidden" name="id_tercero" id="id_tercero" value="<?php echo $datosDoc['id_tercero']; ?>">
                                            </div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <span class="small">CAJA:</span>
                                            </div>
                                            <div class="col-10">
                                                <input type="text" name="referencia" id="referencia" value="<?php echo $datosDoc['nombre_caja'] . ' -> ' . $datosDoc['fecha_ini']; ?>" class="form-control form-control-sm" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-2">
                                                <span class="small">OBJETO:</span>
                                            </div>
                                            <div class="col-10">
                                                <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3" required="required" readonly><?php echo $datosDoc['detalle']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <label for="fecha" class="small">IMPUTACION:</label>
                                            </div>
                                            <div class="col-4">
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="valor" id="valor" value="<?php echo $val_imp ?>" class="form-control" style="text-align: right;" required readonly>
                                                    <div class="input-group-append" id="button-addon4">
                                                        <?php if ($datosDoc['estado'] == 1) { ?>
                                                            <a class="btn btn-outline-success" onclick="ImputacionCtasCajas(<?php echo $datosDoc['id_caja_const'] ?>)"><span class="fas fa-plus fa-lg"></span></a>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <label class="small">FORMA DE PAGO :</label>
                                            </div>
                                            <div class="col-4">
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="forma_pago" id="forma_pago" value="<?php echo $valor_pago; ?>" class="form-control" style="text-align: right;" readonly>
                                                    <div class="input-group-append">
                                                        <?php if ($datosDoc['estado'] == 1) { ?>
                                                            <a class="btn btn-outline-primary" onclick="cargaFormaPago(<?php echo $id_cop; ?>,0)"><span class="fas fa-wallet fa-lg"></span></a>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($datosDoc['estado'] == 1) { ?>
                                            <div class="row ">
                                                <div class="col-2">
                                                    <div><label for="fecha" class="small"></label></div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-align: center">
                                                        <button type="button" class="btn btn-primary btn-sm" onclick="generaMovimientoCaja('<?php echo $id_doc_pag; ?>')">Generar movimiento</button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <input type="hidden" id="id_ctb_doc" name="id_ctb_doc" value="<?php echo $id_doc_pag; ?>">
                                <table id="tableMvtoContableDetallePag" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th style="width: 35%;">Cuenta</th>
                                            <th style="width: 35%;">Tercero</th>
                                            <th style="width: 10%;">Debito</th>
                                            <th style="width: 10%;">Credito</th>
                                            <th style="width: 10%;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modificartableMvtoContableDetallePag">
                                    </tbody>
                                    <?php if ($datosDoc['estado'] == '1') { ?>
                                        <tr>
                                            <td>
                                                <input type="text" name="codigoCta" id="codigoCta" class="form-control form-control-sm" value="" required>
                                                <input type="hidden" name="id_codigoCta" id="id_codigoCta" class="form-control form-control-sm" value="0">
                                                <input type="hidden" name="tipoDato" id="tipoDato" value="0">
                                            </td>
                                            <td><input type="text" name="bTercero" id="bTercero" class="form-control form-control-sm" required>
                                                <input type="hidden" name="idTercero" id="idTercero" value="0">
                                            </td>
                                            <td>
                                                <input type="text" name="valorDebito" id="valorDebito" class="form-control form-control-sm text-right" value="0" required onkeyup="valorMiles(id)" onchange="llenarCero(id)">
                                            </td>
                                            <td>
                                                <input type="text" name="valorCredito" id="valorCredito" class="form-control form-control-sm text-right" value="0" required onkeyup="valorMiles(id)" onchange="llenarCero(id)">
                                            </td>
                                            <td class="text-center">
                                                <button text="0" class="btn btn-primary btn-sm" onclick="GestMvtoDetallePag(this)">Agregar</button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                            </div>
                        </form>
                    </div>

                    </table>
                    <div class="text-center pt-4">
                        <a type="button" class="btn btn-primary btn-sm" onclick="imprimirFormatoTes(<?php echo $id_doc_pag; ?>);" style="width: 5rem;"> <span class="fas fa-print "></span></a>
                        <a onclick="terminarDetalleTes(<?php echo $tipo_dato; ?>,<?php echo $tipo_var; ?>)" class="btn btn-danger btn-sm" style="width: 7rem;" href="#"> Terminar</a>
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
    <!-- Script -->
    <script>
        window.onload = function() {
            buscarConsecutivoTeso('<?php echo $tipo_dato; ?>');
        }
    </script>

</body>

</html>