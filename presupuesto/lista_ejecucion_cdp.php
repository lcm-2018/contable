<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../financiero/consultas.php';
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../head.php';
// Tabla que genera el reporte datos_detalle_cdp.php
// Consulta tipo de presupuesto en la base de datos
$automatico = '';
$id_ppto = $_POST['id_ejec'];
$valoradq = '';

$id_adq = isset($_POST['id_adq']) ? $_POST['id_adq'] : 0;
$id_otro = isset($_POST['id_otro']) ? $_POST['id_otro'] : 0;
$id_cdp = isset($_POST['id_cdp']) ? $_POST['id_cdp'] : 0;

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_pto_cdp`,`fecha`, `id_manu`,`objeto`,`num_solicitud` 
            FROM `pto_cdp` 
            WHERE `id_pto_cdp` = $id_cdp";
    $rs = $cmd->query($sql);
    $datosCdp = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if ($id_cdp == 0) {
    $valida = true;
} else {
    $valida = false;
}
if (empty($datosCdp)) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT
                    MAX(`id_manu`) AS `id_manu` 
                FROM
                    `pto_cdp`
                WHERE (`id_pto` = $id_ppto)";
        $rs = $cmd->query($sql);
        $consecutivo = $rs->fetch();
        $id_manu = !empty($consecutivo) ? $consecutivo['id_manu'] + 1 : 1;
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    $datosCdp['id_pto_cdp'] = 0;
    $datosCdp['fecha'] = '';
    $datosCdp['id_manu'] = $id_manu;
    $datosCdp['objeto'] = '';
    $datosCdp['num_solicitud'] = '';
}
$automatico = 'readonly';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT (SUM(`valor`) - SUM(`valor_liberado`)) as `valorCdp` FROM `pto_cdp_detalle` WHERE `id_pto_cdp` = $id_cdp";
    $rs = $cmd->query($sql);
    $totalCdp = $rs->fetch();
    // total con puntos de mailes number_format()
    $valor = !empty($totalCdp['valorCdp']) ? $totalCdp['valorCdp'] : 0;
    $total = number_format($valor, 2, '.', ',');
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Buscar si el usuario tiene registrado fecha de sesion
try {
    $sql = "SELECT `fecha` FROM `tb_fin_fecha` WHERE `id_usuario` = '$_SESSION[id_user]' AND vigencia = '$_SESSION[vigencia]'";
    $res = $cmd->query($sql);
    $fechases = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

if ($id_cdp == 0) {
    $fecha = date('Y-m-d');
} else {
    $fecha = date('Y-m-d', strtotime($datosCdp['fecha']));
}
// si el proceso llega de otro si consulto el id de la adquisición
if ($id_otro != 0) {
    $sql = "SELECT
                    `ctt_adquisiciones`.`id_adquisicion` AS `id_adq`
                    , `ctt_novedad_adicion_prorroga`.`val_adicion` AS `val_adicion`
                FROM
                    `ctt_contratos`
                    INNER JOIN `ctt_adquisiciones` 
                        ON (`ctt_contratos`.`id_compra` = `ctt_adquisiciones`.`id_adquisicion`)
                    INNER JOIN `ctt_novedad_adicion_prorroga` 
                        ON (`ctt_novedad_adicion_prorroga`.`id_adq` = `ctt_contratos`.`id_contrato_compra`)
                WHERE (`ctt_novedad_adicion_prorroga`.`id_nov_con` = $id_otro)";
    $res = $cmd->query($sql);
    $datosOtro = $res->fetch();
    $id_adq = $datosOtro['id_adq'];
    $valorotro = $datosOtro['val_adicion'];
}
// Si el proceso viene de adquisiciones llama el objeto y valida fecha
$objeto = $datosCdp['objeto'];
if ($id_adq > 0) {
    // consulto datos de ctt_adquisiciones donde id_adq sea igual a id_adquisiciones
    $sql = "SELECT `objeto`,`fecha_adquisicion`,`val_contrato` FROM `ctt_adquisiciones` WHERE `id_adquisicion` = $id_adq";
    $res = $cmd->query($sql);
    $datosAdq = $res->fetch();
    $objeto = $datosAdq['objeto'];
    $valoradq = $datosAdq['val_contrato'];
}
if ($id_otro > 0) {
    $objeto = "OTRO SI " . $objeto;
    $valoradq = $valorotro;
}
// Consulta funcion fechaCierre del modulo 4
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 4, $cmd);
$cmd = null;
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
?>

<body class="sb-nav-fixed <?php echo $_SESSION['navarlat'] === '1' ? 'sb-sidenav-toggled' : '' ?>">

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
                                    DETALLE CERTIFICADO DE DISPONIBILIDAD PRESUPUESTAL
                                </div>


                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div id="divFormDoc">
                                <form id="formAddEjecutaPresupuesto">
                                    <input type="hidden" id="id_pto_presupuestos" name="id_pto_presupuestos" value="<?php echo $id_ppto; ?>">
                                    <input type="hidden" id="id_adq" name="id_adq" value="<?php echo $id_adq; ?>">
                                    <input type="hidden" id="id_otro" name="id_otro" value="<?php echo $id_otro; ?>">

                                    <input type="hidden" id="id_pto_docini" value="<?php echo $datosCdp['id_manu']; ?>">
                                    <div class="right-block">
                                        <div class="row pb-1">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">NUMERO CDP:</label></div>
                                            </div>
                                            <div class="col-10">
                                                <input type="number" name="numCdp" id="numCdp" class="form-control form-control-sm" value="<?php echo $datosCdp['id_manu']; ?>" onchange="buscarCdp(value,'CDP')" <?php echo $automatico; ?> readonly>
                                            </div>
                                            <!--
                                            <div class="col-2" style="margin: 0px; padding: 0px;">
                                                <div class=""><a value="' . $id_pto . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb" title="Lista de CDP " id="botonListaCdp"><span class="far fa-list-alt fa-lg"></span></a></div>
                                            </div>-->
                                        </div>
                                        <div class="row pb-1">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">FECHA:</label></div>
                                            </div>
                                            <div class="col-10">
                                                <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" min="<?php echo $fecha_cierre; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha; ?>" onchange="buscarConsecutivo('CDP');" readonly>
                                            </div>
                                        </div>
                                        <div class="row pb-1">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">OBJETO:</label></div>
                                            </div>
                                            <div class="col-10"> <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3" required="required" readonly><?php echo $objeto; ?></textarea></div>
                                        </div>
                                        <div class="row pb-1">
                                            <div class="col-2">
                                                <div class="col"><label for="sol" class="small">No SOLICITUD:</label></div>
                                            </div>
                                            <div class="col-10">
                                                <input type="text" name="solicitud" id="solicitud" class="form-control form-control-sm" value="<?php echo $datosCdp['num_solicitud']; ?>">
                                            </div>
                                        </div>

                                    </div>
                                </form>
                            </div>
                            <form id="formAddModDetalleCDP">
                                <?php
                                if ($id_cdp == 0) {
                                    echo '<input type="hidden" id="valida" value="0">';
                                }
                                ?>
                                <input type="hidden" id="id_cdp" name="id_cdp" value="<?php echo $id_cdp; ?>">
                                <input type="hidden" id="id_pto_cdp" name="id_pto_cdp" value="<?php echo $id_ppto; ?>">
                                <input type="hidden" id="id_pto_movto" name="id_pto_movto" value="<?php echo $id_ppto; ?>">
                                <table id="tableEjecCdp" class="table table-striped table-bordered table-sm table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th style="width: 8%;">ID</th>
                                            <th style="width: 60%;">Codigo</th>
                                            <th style="width: 20%;" class="text-center">Valor</th>
                                            <th style="width: 12%;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modificarEjecCdp">
                                    </tbody>
                                </table>
                            </form>
                            <div class="text-center pt-4">
                                <?php if (PermisosUsuario($permisos, 5401, 6) || $id_rol == 1) { ?>
                                    <a type="button" class="btn btn-primary btn-sm" onclick="imprimirFormatoCdp(<?php echo $id_cdp; ?>);" style="width: 5rem;"> <span class="fas fa-print "></span></a>
                                <?php } ?>
                                <a type="button" id="volverListaCdps" class="btn btn-danger btn-sm" style="width: 5rem;" href="#"> VOLVER</a>

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
</body>

</html>