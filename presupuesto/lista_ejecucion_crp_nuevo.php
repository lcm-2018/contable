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
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../head.php';
// Consulta tipo de presupuesto
$id_crp = isset($_POST['id_crp']) ? $_POST['id_crp'] : 0;
$id_cdp = isset($_POST['id_cdp']) ? $_POST['id_cdp'] : 0;
$id_pto = $_POST['id_pto'];
$vigencia = $_SESSION['vigencia'];
$automatico = '';
// Consulto los datos generales del nuevo registro presupuesal
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                MAX(`id_manu`) AS `id_manu` 
            FROM
                `pto_crp`
            WHERE (`id_pto` = $id_pto)";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    $id_manu = !empty($consecutivo) ? $consecutivo['id_manu'] + 1 : 1;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
            `objeto`,`fecha`
            FROM `pto_cdp`
            WHERE `id_pto_cdp` = $id_cdp";
    $rs = $cmd->query($sql);
    $objeto_ = $rs->fetch();
    $objeto = !empty($objeto_) ? $objeto_['objeto'] : '';
    $fecha_cdp = !empty($objeto_) ? $objeto_['fecha'] : date('Y-m-d');
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_adquisiciones`.`id_cdp`
                , `ctt_adquisiciones`.`id_tercero`
                , `tb_terceros`.`nit_tercero`
                , `tb_terceros`.`nom_tercero`
                , `ctt_contratos`.`num_contrato`
            FROM
                `ctt_adquisiciones`
                INNER JOIN `tb_terceros` 
                    ON (`ctt_adquisiciones`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                LEFT JOIN `ctt_contratos` 
                    ON (`ctt_adquisiciones`.`id_adquisicion` = `ctt_contratos`.`id_compra`)
            WHERE (`ctt_adquisiciones`.`id_cdp` = $id_cdp)
            UNION ALL
            SELECT
                `ctt_novedad_adicion_prorroga`.`id_cdp`
                , `ctt_adquisiciones`.`id_tercero`
                , `tb_terceros`.`nit_tercero`
                , `tb_terceros`.`nom_tercero`
                , `ctt_contratos`.`num_contrato`
            FROM
                `ctt_contratos`
                INNER JOIN `ctt_novedad_adicion_prorroga` 
                    ON (`ctt_contratos`.`id_contrato_compra` = `ctt_novedad_adicion_prorroga`.`id_adq`)
                INNER JOIN `ctt_adquisiciones` 
                    ON (`ctt_contratos`.`id_compra` = `ctt_adquisiciones`.`id_adquisicion`)
                INNER JOIN `tb_terceros` 
                    ON (`ctt_adquisiciones`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
            WHERE (`ctt_novedad_adicion_prorroga`.`id_cdp` = $id_cdp)";
    $rs = $cmd->query($sql);
    $ctt = $rs->fetch();
    $id_ter = !empty($ctt) ? $ctt['id_tercero'] : 0;
    $num_contrato = !empty($ctt) ? $ctt['num_contrato'] : '';
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_pto`,`fecha`, `id_manu`,`objeto`, `id_tercero_api`, `num_contrato` FROM `pto_crp` WHERE `id_pto_crp` = $id_crp";
    $rs = $cmd->query($sql);
    $datosCRP = $rs->fetch();
    if (empty($datosCRP)) {
        $datosCRP['id_pto'] = '';
        $datosCRP['fecha'] = date('Y-m-d');
        $datosCRP['id_manu'] = $id_manu;
        $datosCRP['objeto'] = $objeto;
        $datosCRP['num_contrato'] = $num_contrato;
        $datosCRP['id_tercero_api'] = 0;
    } else {
        $automatico = 'readonly';
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto si el Cdp esta relacionado en el campo cdp de la tabla ctt_novedad_adicion_prorroga
$id_t = [$datosCRP['id_tercero_api']];

$ids = implode(',', $id_t);
$terceros = getTerceros($ids, $cmd);
$cmd = null;
//$terceros = array_merge($terceros, getTerceros($id_ter, $cmd));
if ($id_ter == 0) {
    if ($datosCRP['id_tercero_api'] == 0) {
        $tercero = '---';
        $ccnit = '---';
    } else {
        $key = array_search($datosCRP['id_tercero_api'], array_column($terceros, 'id_tercero_api'));
        $tercero = $key !== false ? $terceros[$key]['nom_tercero'] : '---';
        $ccnit = $key !== false ? $terceros[$key]['nit_tercero'] : '---';
    }
} else {
    $tercero = $ctt['nom_tercero'];
    $ccnit = $ctt['nit_tercero'];
    $datosCRP['id_tercero_api'] = $id_ter;
}
$fecha_cierre =  date("Y-m-d", strtotime($fecha_cdp));
$fecha_max = date("Y-m-d", strtotime($vigencia . '-12-31'));
?>

<body class="sb-nav-fixed <?php echo $_SESSION['navarlat'] === '1' ? 'sb-sidenav-toggled' : ''; ?>">

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
                        <form id="formGestionaCrp">
                            <div class="card-body" id="divCuerpoPag">
                                <div>
                                    <div class="right-block">
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">NUMERO CRP:</label></div>
                                            </div>
                                            <div class="col-6 pb-1"><input type="number" name="numCdp" id="numCdp" class="form-control form-control-sm" value="<?php echo $datosCRP['id_manu']; ?>" <?php echo $automatico; ?>>
                                                <input type="hidden" id="id_pto_ppto" name="id_pto_presupuestos" value="<?php echo $id_pto; ?>">

                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">FECHA:</label></div>
                                            </div>
                                            <div class="col-6 pb-1"><input type="date" name="fecha" id="fecha" class="form-control form-control-sm" min="<?php echo $fecha_cierre; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo  date('Y-m-d', strtotime($datosCRP['fecha'])); ?>" <?php echo $automatico; ?>></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">TERCERO:</label></div>
                                            </div>
                                            <input type="hidden" name="id_tercero" id="id_tercero" value="<?php echo $datosCRP['id_tercero_api']; ?>">
                                            <div class="col-6 pb-1"><input type="text" id="tercero" class="form-control form-control-sm" value="<?php echo $tercero; ?>" required <?php echo $automatico; ?>>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">OBJETO:</label></div>
                                            </div>
                                            <div class="col-6 pb-1"><textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3" required="required" <?php echo $automatico; ?>><?php echo $datosCRP['objeto']; ?></textarea></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">NO CONTRATO:</label></div>
                                            </div>
                                            <div class="col-6 pb-1"><input type="text" name="contrato" id="contrato" class="form-control form-control-sm" value="<?php echo $datosCRP['num_contrato']; ?>" <?php echo $automatico; ?>></div>
                                        </div>

                                    </div>
                                </div>
                                <br>
                                <input type="hidden" name="id_cdp" id="id_cdp" value="<?php echo $id_cdp; ?>">
                                <input type="hidden" name="id_crp" id="id_crp" value="<?php echo $id_crp ?>">
                                <?php if (PermisosUsuario($permisos, 5401, 2) || $id_rol == 1) {
                                    echo  '<input type="hidden" id="peReg" value="1">';
                                } else {
                                    echo  '<input type="hidden" id="peReg" value="0">';
                                }
                                ?>
                                <table id="tableEjecCrpNuevo" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Codigo</th>
                                            <th>Valor</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modificarEjecCrpNuevo">
                                    </tbody>
                                </table>

                            </div>
                        </form>
                        <div class="text-center p-4">
                            <?php if (PermisosUsuario($permisos, 5401, 2) || $id_rol == 1) {
                                $opcion = $id_crp == 0 ? 'Registrar' : 'Actualizar';
                                $text = $id_crp == 0 ? 1 : 2;
                                echo '<button class="btn btn-info btn-sm" id="registrarMovDetalle" text="' . $text . '">' . $opcion . '</button>';
                            } ?>
                            <a value="" type="button" class="btn btn-primary btn-sm" onclick="imprimirFormatoCrp(<?php echo $id_crp ?>)" style="width: 5rem;"> <span class="fas fa-print "></span></a>
                            <a onclick="cambiaListado(2)" class="btn btn-danger btn-sm" style="width: 7rem;" href="#"> VOLVER</a>
                        </div>
                        <input type="hidden" name="id_pto_save" id="id_pto_save" value="">

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