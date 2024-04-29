<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../financiero/consultas.php';
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../head.php';
// Consulta tipo de presupuesto
$id_doc = $_POST['id_doc'] ?? 0;

$id_cop = $_POST['id_cop'] ?? 0;
$tipo_dato = $_POST['tipo_dato'] ?? 0;
$tipo_mov = $_POST['tipo_movi'] ?? '0';
$tipo_var = $_POST['tipo_var'] ?? '0';
$id_arq = $_POST['id_arq'] ?? '0';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

// Variables que no llegan de presupuesto
$dif = 0;
$debito = 0;
$credito = 0;
$id_manu = '';
$valor_causado = 0;
$id_crpp = 0;
$id_ter = '';
$objeto = '';
$valor_teso = 0;
$fecha_doc = date('Y-m-d');
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 6, $cmd);
$fecha_cierre = strtotime('+1 day', strtotime($fecha_cierre));
$fecha_cierre = date('Y-m-d', $fecha_cierre);
$fecha = fechaSesion($_SESSION['vigencia'], $_SESSION['id_user'], $cmd);
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
$fecha_fact = $fecha;
$fecha_ven = strtotime('+30 day', strtotime($fecha_doc));
$fecha_ven = date('Y-m-d', $fecha_ven); // fecha final con 30 dias sumados

if (isset($_POST['id_doc'])) {
    try {
        $sql = "SELECT id_ctb_doc,fecha,id_manu,detalle,id_tercero,tipo_doc,id_ref FROM ctb_doc WHERE id_ctb_doc=$id_doc";
        $rs = $cmd->query($sql);
        $datosMov = $rs->fetch();
        $tipo_dato2 = $datosMov['tipo_doc'];
        $fecha_doc = $datosMov['fecha'];
        $fecha_doc = date('Y-m-d', strtotime($fecha_doc));
        $objeto = $datosMov['detalle'];
        $id_ter = $datosMov['id_tercero'];
        $id_manu = $datosMov['id_manu'];
        $id_ref = $datosMov['id_ref'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }

    try {
        $sql = "SELECT sum(debito) as debito, sum(credito) as credito FROM ctb_libaux WHERE id_ctb_doc=$id_doc GROUP BY id_ctb_doc";
        $rs = $cmd->query($sql);
        $sumaMov = $rs->fetch();
        $debito = $sumaMov['debito'];
        $credito = $sumaMov['credito'];
        $dif = $sumaMov['debito'] - $sumaMov['credito'];
        $tipo_dato2 = $datosMov['tipo_doc'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }

    $fecha = date('Y-m-d', strtotime($datosMov['fecha']));
    // Consulto el valor de id_pto_doc en pto_documento_detalles cuando id_ctb_doc es igual a $id_doc
    try {
        $sql = "SELECT id_pto_doc,id_ctb_cop FROM pto_documento_detalles WHERE id_ctb_doc=$id_doc";
        $rs = $cmd->query($sql);
        $datosCrp = $rs->fetch();
        $id_crpp = $datosCrp['id_pto_doc'];
        $id_cop = $datosCrp['id_ctb_cop'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    // Consulto el valor de causacion de costo asociado al registro en la tabla ctb_causa_costos
    try {
        $sql = "SELECT id_ctb_pag FROM seg_tes_detalle_pago WHERE id_ctb_doc=$id_doc";
        $rs = $cmd->query($sql);
        $detalle_pago = $rs->fetch();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}

// Si la solicitud de causación esta asociada a un registro presupuestal consultamos los datos del registro
if (isset($_POST['id_cop'])) {
    try {
        $sql = "SELECT
        `pto_documento`.`id_tercero`
        , `pto_documento`.`fecha`
        , `pto_documento`.`objeto`
        , `pto_documento_detalles`.`id_documento`
        , `pto_documento_detalles`.`id_ctb_doc`
    FROM
        `pto_documento_detalles`
        INNER JOIN `pto_documento` 
            ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
    WHERE (`pto_documento_detalles`.`id_ctb_doc` =$id_cop);";
        $rs = $cmd->query($sql);
        $datosCrp = $rs->fetch();
        $id_ter = $datosCrp['id_tercero'];
        $fecha_doc = date('Y-m-d'); //, strtotime($datosCrp['fecha']));
        $objeto = $datosCrp['objeto'];
        $id_crpp = $datosCrp['id_pto_doc'];
        $fecha_fact = $fecha_doc;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    // consulto el objeto de la tabla ctb_doc cuando id_ctb_doc = a $datosCrp['id_ctb_doc']
    try {
        $sql = "SELECT detalle FROM ctb_doc WHERE id_ctb_doc={$datosCrp['id_ctb_doc']};";
        $rs = $cmd->query($sql);
        $datos_detalle = $rs->fetch();
        $objeto = $datos_detalle['detalle'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
// consulto el valor causado valor en la tabla pto_documento_detalles
try {
    $sql = "SELECT sum(valor) as valor FROM pto_documento_detalles WHERE id_ctb_doc=$id_doc AND tipo_mov='PAG'";
    $rs = $cmd->query($sql);
    $sumaCausado = $rs->fetch();
    $valor_causado = number_format($sumaCausado['valor'], 2, '.', ',');
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto el valor total de seg_tes_detalle_pago cuando id_ctb_doc es igual a $id_doc
try {
    $sql = "SELECT sum(valor) as valor FROM seg_tes_detalle_pago WHERE id_ctb_doc=$id_doc GROUP BY id_ctb_doc";
    $rs = $cmd->query($sql);
    $sumaPagado = $rs->fetch();
    $valor_pago = number_format($sumaPagado['valor'], 2, '.', ',');
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consultar los documentos de referencia que estan asociados al comprobante
try {
    $sql = "SELECT
                `seg_ctb_referencia`.`id_ctb_referencia`
                , `seg_ctb_referencia`.`nombre`
            FROM
                `seg_ctb_referencia`
                INNER JOIN `ctb_fuente` 
                    ON (`seg_ctb_referencia`.`id_ctb_fuente` = `ctb_fuente`.`id_doc_fuente`)
            WHERE (`ctb_fuente`.`cod` ='$tipo_dato');";
    $rs = $cmd->query($sql);
    $referencia = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

// Cuando el documento viene de una lista que arqueo de caja para consignación consulto los datos para precargar formulario
if ($tipo_dato == 'CICP' || $tipo_dato == 'CTCB') {
    if ($tipo_dato == 'CICP') {
        $id_arq = $id_doc;
    }
    try {
        $sql = "SELECT
                    `seg_tes_causa_arqueo`.`id_causa_arqueo`
                    ,`ctb_doc`.`id_ctb_doc`
                    ,`ctb_doc`.`id_manu`
                    , `ctb_doc`.`fecha`
                    , `ctb_doc`.`id_tercero`
                    , `ctb_doc`.`detalle`
                    , SUM(`seg_tes_causa_arqueo`.`valor_arq`) as valor
                FROM
                    `seg_tes_causa_arqueo`
                    INNER JOIN `ctb_doc` 
                        ON (`seg_tes_causa_arqueo`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                WHERE `seg_tes_causa_arqueo`.`id_ctb_doc` =$id_arq;";
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

// Consulta terceros en la api ********************************************* API
$url = $api . 'terceros/datos/res/datos/id/' . $id_ter;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_api = curl_exec($ch);
curl_close($ch);
$dat_ter = json_decode($res_api, true);

$tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
// fin api terceros ******************************************************** 
if ($dat_ter == null) {
    $tercero = '';
}
if ($_POST['tipo_dato'] == 'CEVA') {
    $fecha = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha_doc = $fecha->format('Y-m-d');
}
if ($_POST['tipo_dato'] == 'CTCB') {
    $fecha_doc = date("Y-m-d", strtotime($fecha_arq));
    // consulto la fecha del recibo de caja para
    try {
        $sql = "SELECT
                    `ctb_doc`.`fecha`
                FROM
                    `ctb_doc`
                WHERE `ctb_doc`.`id_ctb_doc` =$id_arq;";
        echo $sql;
        $rs = $cmd->query($sql);
        $fecha_recibo = $rs->fetch();
        $fecha_doc = new DateTime($fecha_recibo['fecha']);
        // pasar a formato y-m-d
        $fecha_doc = $fecha_doc->format('Y-m-d');
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
// consultar si estado es 0 en tes_referencia
try {
    $sql = "SELECT numero FROM  tes_referencia  WHERE estado =0;";
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
$ter_reonly = '';
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
                                    DETALLE DEL COMPROBANTE <?php echo ''; ?>
                                </div>
                            </div>
                        </div>
                        <!-- Formulario para nuevo reistro -->
                        <input type="hidden" id="peReg" value="<?php echo $permisos['registrar']; ?>">
                        <input type="hidden" id="valor_teso" value="<?php echo $valor_teso; ?>">
                        <form id="formAddDetallePag">
                            <div class="card-body" id="divCuerpoPag">
                                <div>
                                    <div class="right-block">
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="numDoc" class="small">NUMERO ACTO: </label></div>
                                            </div>
                                            <div class="col-2"><input type="number" name="numDoc" id="numDoc" class="form-control form-control-sm" value="<?php echo $id_manu; ?>" required readonly>
                                                <input type="hidden" id="tipodato" name="tipodato" value="<?php echo $tipo_dato; ?>">
                                                <input type="hidden" id="id_crpp" name="id_crpp" value="<?php echo $id_crpp; ?>">
                                                <input type="hidden" id="id_cop_pag" name="id_cop_pag" value="<?php echo $id_cop; ?>">
                                                <input type="hidden" id="id_arqueo" name="id_arqueo" value="<?php echo $id_arq; ?>">

                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">FECHA:</label></div>
                                            </div>
                                            <div class="col-2"> <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" min="<?php echo $fecha_cierre; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_doc; ?>" onchange="buscarConsecutivoCont('<?php echo $tipo_dato; ?>');"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">TERCERO:</label></div>
                                            </div>
                                            <div class="col-6"><input type="text" name="tercero" id="tercero" class="form-control form-control-sm" value="<?php echo $tercero; ?>" required <?php echo $ter_reonly; ?>>
                                                <input type="hidden" name="id_tercero" id="id_tercero" value="<?php echo $id_ter; ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">OBJETO:</label></div>
                                            </div>
                                            <div class="col-10"><textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3" required="required"><?php echo $objeto; ?></textarea></div>
                                        </div>
                                        <?php if ($referencia != null) { ?>
                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><label for="fecha" class="small">CONCEPTO:</label></div>
                                                </div>
                                                <div class="col-4">
                                                    <select name="ref_mov" id="ref_mov" class="form-control form-control-sm" required>
                                                        <option value="0">...Seleccione...</option>
                                                        <?php foreach ($referencia as $rf) :
                                                            if ($id_ref == $rf['id_ctb_referencia']) {
                                                                echo '<option value="' . $rf['id_ctb_referencia'] . '" selected>' . $rf['nombre'] . '</option>';
                                                            } else {
                                                                echo '<option value="' . $rf['id_ctb_referencia'] . '">' . $rf['nombre'] . '</option>';
                                                            }
                                                        ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-2">
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">REFERENCIA :</label></div>
                                            </div>
                                            <div class="col-2"><input type="text" name="referencia" id="referencia" value="<?php echo $ref; ?>" class="form-control form-control-sm" style="text-align: right;"></div>
                                            <div class=" col-2 text-left" style="padding-top: 3px;">&nbsp;
                                                <input type="checkbox" class="custom-control-input" id="checkboxId" onclick="definirReferenciaPago();" <?php echo $chek; ?>>
                                                <label class="custom-control-label" for="checkboxId"></label>
                                            </div>
                                            <div class="col-2">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small"></label></div>
                                            </div>
                                            <div class="col-2"><button type="button" class="btn btn-danger btn-sm" onclick="procesaCausacionPago('<?php echo $id_doc; ?>')">Guardar</button></div>
                                            <div class="col-2">
                                            </div>
                                        </div>
                                        <?php if ($tipo_dato == 'CICP') { ?>
                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><label for="fecha" class="small">ARQUEO DE CAJA:</label></div>
                                                </div>
                                                <div class="col-2"><input type="text" name="arqueo_caja" id="arqueo_caja" value="<?php echo $valor_teso; ?>" class="form-control form-control-sm" style="text-align: right;" required readonly></div>
                                                <div class=" col-2 text-left">
                                                    <a class="btn btn-outline-success btn-sm btn-circle shadow-gb" onclick="cargaArqueoCaja('<?php echo $id_cop; ?>')"><span class="fas fa-cash-register fa-lg"></span></a>
                                                </div>
                                                <div class="col-2"></div>
                                            </div>
                                        <?php } ?>
                                        <?php if ($tipo_dato == 'CMLG' || $tipo_dato == 'CMCN' || $tipo_dato == 'CMMT') { ?>
                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><label for="fecha" class="small">CAJA MENOR:</label></div>
                                                </div>
                                                <div class="col-2"><input type="text" name="arqueo_caja" id="arqueo_caja" value="<?php echo $valor_pago; ?>" class="form-control form-control-sm" style="text-align: right;" required readonly></div>
                                                <div class=" col-2 text-left">
                                                    <a class="btn btn-outline-success btn-sm btn-circle shadow-gb" onclick="cargaLegalizacionCajaMenor('<?php echo $id_cop; ?>')"><span class="fas fa-cash-register fa-lg"></span></a>
                                                </div>
                                                <div class="col-2"></div>
                                            </div>
                                        <?php } ?>
                                        <?php if ($tipo_dato == 'CING' || $tipo_dato == 'CTDI' || $tipo_dato == 'CNCR' || $tipo_dato == 'CIVA') { ?>
                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><label for="fecha" class="small">PRESUPUESTO:</label></div>
                                                </div>
                                                <div class="col-2"><input type="text" name="arqueo_caja" id="arqueo_caja" value="<?php echo $valor_pago; ?>" class="form-control form-control-sm" style="text-align: right;" required readonly></div>
                                                <div class=" col-2 text-left">
                                                    <a class="btn btn-outline-success btn-sm btn-circle shadow-gb" onclick="cargaPresupuestoIng('')"><span class="fas fa-plus fa-lg"></span></a>
                                                </div>
                                                <div class="col-2"></div>
                                            </div>
                                        <?php } ?>
                                        <?php
                                        if ($id_cop > 0) {
                                        ?>
                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><label for="fecha" class="small">IMPUTACION:</label></div>
                                                </div>
                                                <div class="col-2"><input type="text" name="valor" id="valor" value="<?php echo $valor_causado; ?>" class="form-control form-control-sm" style="text-align: right;" required readonly></div>
                                                <div class=" col-2 text-left">
                                                    <a class="btn btn-outline-success btn-sm btn-circle shadow-gb" onclick="cargaListaCausaciones('<?php echo $id_cop; ?>')"><span class="fas fa-plus fa-lg"></span></a>
                                                    <a class="btn btn-outline-secondary btn-sm btn-circle shadow-gb" onclick="cargaListaInputaciones('<?php echo $id_cop; ?>')"><span class="fas fa-search fa-lg"></span></a>

                                                </div>
                                                <div class="col-2">
                                                </div>
                                            </div>

                                        <?php
                                        }
                                        if ($tipo_dato == 'CIVA') {
                                            $campo_req = "";
                                        } else {
                                            $campo_req = "readonly";
                                        }
                                        ?>

                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">FORMA DE PAGO:</label></div>
                                            </div>
                                            <div class="col-2"><input type="text" name="forma_pago" id="forma_pago" value="<?php echo $valor_pago; ?>" class="form-control form-control-sm" style="text-align: right;" required <?php echo $campo_req; ?>></div>
                                            <div class=" col-2 text-left">
                                                <a class="btn btn-outline-primary btn-sm btn-circle shadow-gb" onclick="cargaFormaPago('<?php echo $id_cop; ?>')"><span class="fas fa-wallet fa-lg"></span></a>
                                            </div>
                                            <div class="col-2"></div>
                                        </div>


                                        <div class="row ">
                                            <div class="col-2">
                                                <div><label for="fecha" class="small"></label></div>
                                            </div>
                                            <div class="col-2">
                                                <div class="text-align: center">
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="generaMovimientoPag('<?php echo $id_doc; ?>')">Generar movimiento</button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <br>
                                <table id="tableMvtoContableDetallePag" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th style="width: 55%;">Cuenta</th>
                                            <th style="width: 15%;">Debito</th>
                                            <th style="width: 15%;">Credito</th>
                                            <th style="width: 15%;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modificartableMvtoContableDetallePag">

                                    </tbody>


                                    <tr>
                                        <th>
                                            <input type="hidden" id="id_ctb_doc" name="id_ctb_doc" value="<?php echo $id_doc; ?>">
                                            <input type="text" name="codigoCta" id="codigoCta" class="form-control form-control-sm" value="" required>
                                            <input type="hidden" name="id_codigoCta" id="id_codigoCta" class="form-control form-control-sm" value="">
                                        </th>
                                        <th>
                                            <input type="text" name="valorDebito" id="valorDebito" class="form-control form-control-sm" size="6" value="" style="text-align: right;" required ondblclick="sumasIguales()" onkeyup="valorMiles(id)" onchange="llenarCero(id)">
                                        </th>
                                        <th>
                                            <input type="text" name="valorCredito" id="valorCredito" class="form-control form-control-sm" size="6" value="" style="text-align: right;" required ondblclick="sumasIguales()" onkeyup="valorMiles(id)" onchange="llenarCero(id)">
                                        </th>
                                        <th class="text-center">
                                            <button type="submit" class="btn btn-primary btn-sm" id="registrarMvtoDetalle">Agregar</button>
                                            <input type="hidden" id="id_editar" name="id_editar" value="">
                                        </th>
                                    </tr>
                            </div>
                        </form>
                    </div>
                    <tfoot>
                        <tr>
                            <th>Sumas iguales</th>
                            <th>
                                <div class="text-right">
                                    <input type="text" id="debito" name="debito" value="<?php echo number_format($debito, 2, '.', ',');  ?>" style="background-color:transparent;border: 0;text-align:right;" readonly>
                                </div>
                            </th>
                            <th>
                                <div class="text-right">
                                    <input type="text" id="credito" name="credito" value="<?php echo number_format($credito, 2, '.', ',');  ?>" style="background-color:transparent;border: 0;text-align:right;" readonly>

                                </div>
                            </th>
                            <th>
                                <div class="text-right"></div>
                            </th>
                        </tr>
                        <tr>
                            <th colspan="2">Diferencia</th>

                            <th>
                                <div class="text-right"><input type="text" id="valor_dif" name="valor_dif" value="<?php echo $dif; ?>" style="background-color:transparent;border: 0;text-align:right;" readonly>

                                </div>
                            </th>
                            <th>
                                <div class="text-right"></div>
                            </th>
                        </tr>
                    </tfoot>

                    </table>
                    <div class="text-center pt-4">
                        <a type="button" class="btn btn-primary btn-sm" onclick="imprimirFormatoTes(<?php echo $id_doc; ?>);" style="width: 5rem;"> <span class="fas fa-print "></span></a>
                        <a onclick="terminarDetalleTes('<?php echo $tipo_dato; ?>','<?php echo $tipo_var; ?>')" class="btn btn-danger btn-sm" style="width: 7rem;" href="#"> Terminar</a>
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