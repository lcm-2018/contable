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
/*
cuando se baja id_cop: 87
id_doc: 0
tipo_dato: 4
tipo_var: 1
*/
// Consulta tipo de presupuesto$datosDoc
$id_doc_pag = isset($_POST['id_doc']) ? $_POST['id_doc'] : exit('Acceso no disponible');
$id_cop = isset($_POST['id_cop']) ? $_POST['id_cop'] : 0;
$tipo_dato = isset($_POST['tipo_dato']) ? $_POST['tipo_dato'] : 0; // tiene el id_doc_fuente ej.  7 - nota bancaria
$tipo_mov = isset($_POST['tipo_movi']) ? $_POST['tipo_movi'] : 0;
$tipo_var = isset($_POST['tipo_var']) ? $_POST['tipo_var'] : 0;
$id_arq = isset($_POST['id_arq']) ? $_POST['id_arq'] : 0;
$id_doc_rad = isset($_POST['id_doc_rad']) ? $_POST['id_doc_rad'] : 0;
$id_vigencia = $_SESSION['id_vigencia'];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$fecha_cierre = fechaCierre($_SESSION['vigencia'], 56, $cmd);

if ($id_doc_pag == 0) {
    try {
        $sql = "SELECT
                    MAX(`id_manu`) AS `id_manu` 
                FROM
                    `ctb_doc`
                WHERE (`id_vigencia` = $id_vigencia AND `id_tipo_doc` = $tipo_dato)";
        $rs = $cmd->query($sql);
        $consecutivo = $rs->fetch();
        $id_manu = !empty($consecutivo) ? $consecutivo['id_manu'] + 1 : 1;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    try {
        if ($id_cop > 0) {
            $sql = "SELECT 
                    `ctb_doc`.`id_tercero`
                    , `ctb_doc`.`fecha`
                    , `ctb_doc`.`detalle`
                    , `ctb_doc`.`id_ref`
                    , `tb_terceros`.`nom_tercero`
                    ,  $id_manu AS `id_manu`
                    , `ctb_fuente`.`nombre` AS `fuente`
                    , 0 AS `val_pagado`
                    , 1 AS `estado`
                    , 0 AS `id_ref_ctb`

                FROM `ctb_doc`
                    INNER JOIN `ctb_fuente`
		                ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                    LEFT JOIN `tb_terceros`
                        ON(`ctb_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                WHERE (`ctb_doc`.`id_ctb_doc` =  $id_cop) LIMIT 1";
        } else if ($id_doc_rad > 0) {
            $sql = "SELECT 
                    `ctb_doc`.`id_tercero`
                    , `ctb_doc`.`fecha`
                    , `ctb_doc`.`detalle`
                    , `ctb_doc`.`id_ref`
                    , `tb_terceros`.`nom_tercero`
                    ,  $id_manu AS `id_manu`
                    , `ctb_fuente`.`nombre` AS `fuente`
                    , 0 AS `val_pagado`
                    , 1 AS `estado`
                    , `ctb_doc`.`id_ref_ctb`

                FROM `ctb_doc`
                    INNER JOIN `ctb_fuente`
                        ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                    LEFT JOIN `tb_terceros`
                        ON(`ctb_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                WHERE (`ctb_doc`.`id_ctb_doc` =  $id_doc_rad) LIMIT 1";
        }
        $rs = $cmd->query($sql);
        $datosDoc = $rs->fetch();
        $tercero = $datosDoc['nom_tercero'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
} else {
    $datosDoc = GetValoresCeva($id_doc_pag, $cmd);
    $id_manu = $datosDoc['id_manu'];
    $id_cop = $datosDoc['id_doc_cop'] > 0 ? $datosDoc['id_doc_cop'] : 0;
    $id_ref = $datosDoc['id_ref'];
    if ($id_doc_rad == 0) {
        $sqls = "SELECT
                    `ctb_fuente`.`cod`
                FROM
                    `ctb_doc`
                    INNER JOIN `ctb_fuente` 
                        ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                WHERE (`ctb_doc`.`id_ctb_doc` = {$datosDoc['id_ctb_doc_tipo3']})";
        $rs = $cmd->query($sqls);
        $rdss = $rs->fetch();
        $id_doc_rad = !empty($rdss) && $rdss['cod'] == 'FELE' ? $datosDoc['id_ctb_doc_tipo3'] : 0;
    }
    if ($id_doc_rad > 0) {
        $sql = "SELECT
                SUM(IFNULL(`pto_rec_detalle`.`valor`,0) - IFNULL(`pto_rec_detalle`.`valor_liberado`,0)) AS `valor`
            FROM
                `pto_rec_detalle`
                INNER JOIN `pto_rec` 
                    ON (`pto_rec_detalle`.`id_pto_rac` = `pto_rec`.`id_pto_rec`)
            WHERE (`pto_rec`.`estado` > 0 AND `pto_rec`.`id_ctb_doc` = $id_doc_pag)";
        $rs = $cmd->query($sql);
        $valor = $rs->fetch();
        $datosDoc['val_pagado'] = !empty($valor) ? $valor['valor'] : 0;
    }
    if (!empty($datosDoc)) {
        $id_t = ['0' => $datosDoc['id_tercero']];
        $ids = implode(',', $id_t);
        $dat_ter = getTerceros($ids, $cmd);
        $tercero = !empty($dat_ter) ? $dat_ter[0]['nom_tercero'] : '---';
    } else {
        $tercero = '---';
    }
}
$datosDoc['id_ref_ctb'] = $datosDoc['id_ref_ctb'] == '' ? 0 : $datosDoc['id_ref_ctb'];
try {
    $sql = "SELECT
                `id_ctb_doc`
                , SUM(IFNULL(`debito`,0)) AS `debito`
                , SUM(IFNULL(`credito`,0)) AS `credito`
            FROM
                `ctb_libaux`
            WHERE (`id_ctb_doc` = $id_doc_pag)";
    $rs = $cmd->query($sql);
    $totales = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT `numero` FROM `tes_referencia`  WHERE `estado` = 1";
    $rs = $cmd->query($sql);
    $pagos_ref = $rs->fetch();
    if ($rs->rowCount() > 0) {
        $ref = $pagos_ref['numero'];
        $chek = 'checked';
    } else {
        $ref = 0;
        $chek = '';
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$valor_teso = 0;
$valor_pago = 0;
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
if ($tipo_dato == '9') {
    if ($tipo_dato == '9') {
        $id_arq = $id_doc_pag;
    }
    try {
        $sql = "SELECT
                    `tes_causa_arqueo`.`id_causa_arqueo`
                    ,`ctb_doc`.`id_ctb_doc`
                    ,`ctb_doc`.`id_manu`
                    , `ctb_doc`.`fecha`
                    , `ctb_doc`.`id_tercero`
                    , `ctb_doc`.`detalle`
                    , SUM(`tes_causa_arqueo`.`valor_arq`) as valor
                FROM
                    `tes_causa_arqueo`
                    INNER JOIN `ctb_doc` 
                        ON (`tes_causa_arqueo`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                WHERE `tes_causa_arqueo`.`id_ctb_doc` = $id_arq";
        $sql2 = $sql;
        $rs = $cmd->query($sql);
        $arqueo = $rs->fetch();
        //$objeto =  $arqueo['detalle'];
        $fecha_arq = $arqueo['fecha'];
        $valor_teso = $arqueo['valor'];
        $valor_pago = $valor_teso;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
try {
    if ($id_doc_rad == 0) {
        $sql = "SELECT
                    `ctb_referencia`.`id_ctb_referencia`
                    , `ctb_referencia`.`nombre`
                FROM
                    `ctb_referencia`
                    INNER JOIN `ctb_fuente` 
                        ON (`ctb_referencia`.`id_ctb_fuente` = `ctb_fuente`.`id_doc_fuente`)
                WHERE (`ctb_fuente`.`id_doc_fuente` = $tipo_dato)";
    } else {
        $sql = "SELECT `id_ctb_referencia`,`nombre` FROM `ctb_referencia` WHERE `id_ctb_referencia` = {$datosDoc['id_ref_ctb']}";
    }
    $rs = $cmd->query($sql);
    $referencia = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $sql = "SELECT
                ctb_referencia.accion_pto
            FROM
                ctb_referencia
            WHERE ctb_referencia.id_ctb_referencia =" . $datosDoc['id_ref_ctb']
        . " AND id_ctb_fuente = $tipo_dato";
    $rs = $cmd->query($sql);
    $obj_referencia = $rs->fetch();
    if (empty($obj_referencia)) {
        $obj_referencia['accion_pto'] = 0;
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

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
                    <input type="hidden" id="tipo_var" value="<?php echo $tipo_var; ?>">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    DETALLE DEL COMPROBANTE <b><?php echo $datosDoc['fuente']; ?></b>
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
                        <input type="hidden" id="valor_teso" value="<?php echo $valor_teso; ?>">
                        <form id="formGetMvtoTes">
                            <input type="hidden" id="fec_cierre" value="<?php echo $fecha_cierre; ?>">
                            <div class="card-body" id="divCuerpoPag">
                                <div>
                                    <div class="right-block">
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <span class="small">NUMERO ACTO: </span>
                                            </div>
                                            <div class="col-10">
                                                <input type="number" name="numDoc" id="numDoc" class="form-control form-control-sm" value="<?php echo $id_manu; ?>">
                                                <input type="hidden" id="tipodato" name="tipodato" value="<?php echo $tipo_dato; ?>">
                                                <input type="hidden" id="id_cop_pag" name="id_cop_pag" value="<?php echo $id_cop; ?>">
                                                <input type="hidden" id="id_arqueo" name="id_arqueo" value="<?php echo $id_arq; ?>">
                                                <input type="hidden" id="id_doc_rad" name="id_doc_rad" value="<?php echo $id_doc_rad; ?>">
                                                <input type="hidden" id="hd_accion_pto" name="hd_accion_pto" value="<?php echo $obj_referencia['accion_pto']; ?>">
                                            </div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <span for="fecha" class="small">FECHA:</span>
                                            </div>
                                            <div class="col-10">
                                                <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" value="<?php echo date('Y-m-d', strtotime($datosDoc['fecha'])); ?>">
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
                                                <span class="small">CONCEPTO:</span>
                                            </div>
                                            <div class="col-10">
                                                <select name="ref_mov" id="ref_mov" class="form-control form-control-sm" readonly>
                                                    <?php foreach ($referencia as $rf) {
                                                        if ($datosDoc['id_ref_ctb'] == $rf['id_ctb_referencia']) {
                                                            echo '<option value="' . $rf['id_ctb_referencia'] . '" selected>' . $rf['nombre'] . '</option>';
                                                        }
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <span class="small">REFERENCIA:</span>
                                            </div>
                                            <div class="col-10">
                                                <input type="text" name="referencia" id="referencia" value="<?php echo $datosDoc['id_ref']; ?>" class="form-control form-control-sm" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-2">
                                                <span class="small">OBJETO:</span>
                                            </div>
                                            <div class="col-10">
                                                <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3"><?php echo $datosDoc['detalle']; ?></textarea>
                                            </div>
                                        </div>
                                        <?php if ($tipo_dato == '9') { ?>
                                            <div class="row mb-1">
                                                <div class="col-2">
                                                    <label for="arqueo_caja" class="small">ARQUEO DE CAJA:</label>
                                                </div>
                                                <div class="col-4">
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" name="arqueo_caja" id="arqueo_caja" value="<?php echo $valor_teso; ?>" class="form-control form-control-sm" style="text-align: right;" required readonly>
                                                        <div class="input-group-append">
                                                            <?php if ($datosDoc['estado'] == 1 || $tipo_dato == '9') { ?>
                                                                <a class="btn btn-outline-success btn-sm" onclick="CargaArqueoCajaTes(<?= $id_doc_pag; ?>,0)"><span class="fas fa-cash-register fa-lg"></span></a>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }
                                        if ($tipo_dato == '13' || $tipo_dato == '14' || $tipo_dato == '15') { ?>
                                            <div class="row mb-1">
                                                <div class="col-2">
                                                    <label for="arqueo_caja" class="small">CAJA MENOR:</label>
                                                </div>
                                                <div class="col-4">
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" name="arqueo_caja" id="arqueo_caja" value="<?php echo $valor_pago; ?>" class="form-control form-control-sm" style="text-align: right;" required readonly>
                                                        <div class="input-group-append">
                                                            <?php if ($datosDoc['estado'] == 1) { ?>
                                                                <a class="btn btn-outline-success btn-sm" onclick="cargaLegalizacionCajaMenor('<?php echo $id_cop; ?>')"><span class="fas fa-cash-register fa-lg"></span></a>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }
                                        if (($tipo_dato == '6' || $tipo_dato == '16' || $tipo_dato == '7' || $tipo_dato == '12') && $id_doc_rad == 0) { ?>
                                            <div class="row mb-1">
                                                <div class="col-2">
                                                    <label for="arqueo_caja" class="small">PRESUPUESTO:</label>
                                                </div>
                                                <div class="col-4">
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" name="arqueo_caja" id="arqueo_caja" value="<?php echo $valor_pago; ?>" class="form-control form-control-sm" style="text-align: right;" required readonly>
                                                        <div class="input-group-append">
                                                            <?php if ($datosDoc['estado'] == 1) { ?>
                                                                <!--<a class="btn btn-outline-success btn-sm" onclick="cargaPresupuestoIng('')"><span class="fas fa-plus fa-lg"></span></a>-->
                                                                <a class="btn btn-outline-success btn-sm" id="btn_cargar_presupuesto"><span class="fas fa-plus fa-lg"></span></a>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }
                                        if ($id_cop > 0 && $_SESSION['pto'] == '1') {
                                        ?>
                                            <div class="row mb-1">
                                                <div class="col-2">
                                                    <label for="valor" class="small">IMPUTACION:</label>
                                                </div>
                                                <div class="col-4">
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" name="valor" id="valor" value="<?php echo $datosDoc['val_pagado']; ?>" class="form-control" style="text-align: right;" required readonly>
                                                        <div class="input-group-append" id="button-addon4">
                                                            <?php if ($datosDoc['estado'] == 1 && $id_doc_pag > 0) { ?>
                                                                <button class="btn btn-outline-success" onclick="cargaListaCausaciones(this)"><span class="fas fa-plus fa-lg"></span></button>
                                                            <?php
                                                                /*<a class="btn btn-outline-secondary" onclick="cargaListaInputaciones('<?php echo $id_cop;?>')"><span class="fas fa-search fa-lg"></span></a>*/
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        <?php
                                        }
                                        if ($tipo_dato == '12') {
                                            $campo_req = "";
                                        } else {
                                            $campo_req = "readonly";
                                        }
                                        if ($id_doc_rad > 0) {
                                            $forma = 'FORMA DE RECAUDO :';
                                        } else {
                                            $forma = 'FORMA DE PAGO :';
                                        }
                                        ?>

                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <label for="forma_pago" class="small"><?php echo $forma; ?></label>
                                            </div>
                                            <div class="col-4">
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="forma_pago" id="forma_pago" value="<?php echo $valor_pago; ?>" class="form-control" style="text-align: right;" required <?php echo $campo_req; ?>>
                                                    <div class="input-group-append">
                                                        <?php if ($datosDoc['estado'] == 1 && $id_doc_pag > 0) { ?>
                                                            <button class="btn btn-outline-primary" onclick="cargaFormaPago(<?php echo $id_cop; ?>,0,this)"><span class="fas fa-wallet fa-lg"></span></button>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($datosDoc['estado'] == 1) { ?>
                                            <div class="text-center py-2">
                                                <?php
                                                if ($id_doc_pag > 0) {
                                                ?>
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="generaMovimientoPag(this)">Generar movimiento</button>
                                                <?php
                                                }
                                                ?>
                                                <button type="button" class="btn btn-warning btn-sm" id="GuardaDocMvtoPag" text="<?= $id_doc_pag; ?>">Guardar</button>
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
                                    <?php if ($datosDoc['estado'] == '1' && $id_doc_pag > 0) { ?>
                                        <tr>
                                            <td>
                                                <input type="text" name="codigoCta" id="codigoCta" class="form-control form-control-sm" value="" required>
                                                <input type="hidden" name="id_codigoCta" id="id_codigoCta" class="form-control form-control-sm" value="0">
                                                <input type="hidden" name="tipoDato" id="tipoDato" value="0">
                                            </td>
                                            <td><input type="text" name="bTercero" id="bTercero" class="form-control form-control-sm bTercero" required>
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
</body>

</html>