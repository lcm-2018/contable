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
$id_doc = isset($_POST['id_doc']) ? $_POST['id_doc'] : exit('Acceso no permitido');
$tipo_dato = $_POST['tipo_dato'];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

// Variables que no llegan de presupuesto
$fecha_doc = date('Y-m-d');
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 5, $cmd);
$fecha = fechaSesion($_SESSION['vigencia'], $_SESSION['id_user'], $cmd);
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
$fecha_fact = $fecha;
$fecha_ven = strtotime('+30 day', strtotime($fecha_doc));
$fecha_ven = date('Y-m-d', $fecha_ven); // fecha final con 30 dias sumados
try {
    $sql = "SELECT
                `ctb_fuente`.`nombre` AS `fuente`
                , `ctb_doc`.`id_ctb_doc`
                , `ctb_doc`.`fecha`
                , `ctb_doc`.`id_manu`
                , `ctb_doc`.`detalle`
                , `ctb_doc`.`id_tercero`
                , `ctb_doc`.`estado`
            FROM
                `ctb_doc`
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $datosDoc = $rs->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `id_ctb_doc`
                , SUM(IFNULL(`debito`,0)) AS `debito`
                , SUM(IFNULL(`credito`,0)) AS `credito`
            FROM
                `ctb_libaux`
            WHERE (`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $totales = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$fecha = date('Y-m-d', strtotime($datosDoc['fecha']));
// Consulto el valor de id_pto_doc en pto_documento_detalles cuando id_ctb_doc es igual a $id_doc
try {
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `pto_crp`.`id_pto_crp`
                , `pto_crp`.`id_tercero_api`
                , `pto_crp`.`fecha`
                , `pto_crp`.`objeto`
            FROM
                `ctb_doc`
                INNER JOIN `pto_cop_detalle` 
                    ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `pto_crp_detalle` 
                    ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                INNER JOIN `pto_crp` 
                    ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $id_doc) LIMIT 1";
    $rs = $cmd->query($sql);
    $datosCrp = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto el valor de causacion de costo asociado al registro en la tabla ctb_causa_costos
try {
    $sql = "SELECT
                SUM(`valor`) AS `valor`
            FROM
                `ctb_causa_costos`
            WHERE (`id_ctb_doc` = $id_doc AND `estado` = 2)";
    $rs = $cmd->query($sql);
    $sumaCosto = $rs->fetch();
    $valor_costo = number_format(!empty($sumaCosto) ? $sumaCosto['valor'] : 0, 2, '.', ',');
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

// Consulto la causacion de retenciones asociadas al registro en la tabla seg_ctb_retenciones
try {
    $sql = "SELECT
                SUM(`valor_retencion`) AS `valor`
            FROM
                `ctb_causa_retencion`";
    $rs = $cmd->query($sql);
    $sumaRet = $rs->fetch();
    $valor_ret = number_format(!empty($sumaRet) ? $sumaRet['valor'] : 0, 2, '.', ',');
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto el valor causado valor en la tabla pto_documento_detalles
try {
    $sql = "SELECT
                `id_ctb_doc`
                , IFNULL(SUM(`valor`),0) - IFNULL(SUM(`valor_liberado`),0) AS `valor` 
            FROM
                `pto_cop_detalle`
            WHERE (`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $sumaCausado = $rs->fetch();
    $valor_causado = number_format(!empty($sumaCausado) ? $sumaCausado['valor'] : 0, 2, '.', ',');
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto el tipo de documentos en ctb_tipo_doc
try {
    $sql = "SELECT `id_ctb_tipodoc`, `tipo` FROM `ctb_tipo_doc` ORDER BY `tipo` ASC";
    $rs = $cmd->query($sql);
    $tipodoc = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto los datos de la factura que esten asociados seg_ctb_factura
try {
    $sql = "SELECT
                `ctb_factura`.`id_ctb_doc`
                , `ctb_factura`.`id_tipo_doc`
                , `ctb_factura`.`num_doc`
                , `ctb_factura`.`fecha_fact`
                , `ctb_factura`.`fecha_ven`
                , `ctb_factura`.`valor_pago`
                , `ctb_factura`.`valor_iva`
                , `ctb_factura`.`valor_base`
                , `ctb_factura`.`detalle`
                , `ctb_tipo_doc`.`tipo`
            FROM
                `ctb_factura`
                INNER JOIN `ctb_tipo_doc` 
                    ON (`ctb_factura`.`id_tipo_doc` = `ctb_tipo_doc`.`id_ctb_tipodoc`)
            WHERE (`ctb_factura`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $datosFactura = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto tercero registrado en contratación del api de tercero para mostrar el nombre
// Consulta terceros en la api ********************************************* API
if (!empty($datosDoc)) {
    $id_t = ['0' => $datosDoc['id_tercero']];
    $payload = json_encode($id_t);
    //API URL
    $url = $api . 'terceros/datos/res/lista/terceros';
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res_api = curl_exec($ch);
    curl_close($ch);
    $dat_ter = json_decode($res_api, true);
    $tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
} else {
    $tercero = '';
}
$ver = 'readonly';
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
                                    DETALLE DEL MOVIMIENTO CONTABLE <b><?php echo $datosDoc['fuente']; ?></b>
                                </div>
                            </div>
                        </div>
                        <!-- Formulario para nuevo reistro -->
                        <?php
                        if (PermisosUsuario($permisos, 5501, 2) || $id_rol == 1) {
                            echo '<input type="hidden" id="peReg" value="1">';
                        } else {
                            echo '<input type="hidden" id="peReg" value="0">';
                        }
                        ?>
                        <div>
                            <div class="card-body" id="divCuerpoPag">
                                <div>
                                    <div class="right-block">
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <div class="col"><span class="small">NUMERO ACTO:</span></div>
                                            </div>
                                            <div class="col-10"><input type="number" name="numDoc" id="numDoc" class="form-control form-control-sm" value="<?php echo $datosDoc['id_manu']; ?>" required readonly>
                                                <input type="hidden" id="tipodato" name="tipodato" value="<?php echo $tipo_dato; ?>">
                                                <input type="hidden" id="id_crpp" name="id_crpp" value="<?php echo !empty($datosCrp) ? $datosCrp['id_pto_crp'] : 0 ?>">

                                            </div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <div class="col"><span class="small">FECHA:</span></div>
                                            </div>
                                            <div class="col-10"> <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" min="<?php echo $fecha_doc; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_doc; ?>" readonly></div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <div class="col"><span class="small">TERCERO:</span></div>
                                            </div>
                                            <div class="col-10"><input type="text" name="tercero" id="tercero" class="form-control form-control-sm" value="<?php echo $tercero; ?>" readonly>
                                                <input type="hidden" name="id_tercero" id="id_tercero" value="<?php echo $datosDoc['id_tercero'] ?>">
                                            </div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <div class="col"><span class="small">OBJETO:</span></div>
                                            </div>
                                            <div class="col-10"><textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-span="Default select example" rows="3" required="required" readonly><?php !empty($datosCrp) ? $datosCrp['objeto'] : '' ?></textarea></div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <div class="col"><span class="small">DETALLE:</span></div>
                                            </div>
                                            <div class="col-10"><textarea id="detalle" type="text" name="detalle" class="form-control form-control-sm py-0 sm" aria-span="Default select example" rows="2" readonly><?php echo $datosDoc['detalle']; ?></textarea></div>
                                        </div>
                                        <?php
                                        if ($tipo_dato == '3') {
                                        ?>
                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><span class="small"></span></div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-center"><span class="small">Tipo documento:</span></div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-center"><span class="small">Número de documento:</span>
                                                    </div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-center"><span class="small text-center">Fecha factura:</span></div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-center"><span class="small">Fecha vencimiento:</span></div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><span class="small">DOCUMENTO:</span></div>
                                                </div>
                                                <div class="col-2">
                                                    <div>
                                                        <!--Realizo select con los datos de $tipodoc-->
                                                        <select class="form-control form-control-sm" id="tipoDoc" name="tipoDoc" onchange="consecutivoDocEqui(value);" required>
                                                            <option value="">-- Selecionar --</option>
                                                            <?php foreach ($tipodoc as $tipo) :
                                                                if ($tipo_doc == $tipo['id_ctb_tipodoc']) {
                                                                    echo '<option value="' . $tipo['id_ctb_tipodoc'] . '" selected>' . $tipo['tipo'] . '</option>';
                                                                } else {
                                                                    echo '<option value="' . $tipo['id_ctb_tipodoc'] . '">' . $tipo['tipo'] . '</option>';
                                                                }
                                                            endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-2">
                                                    <div><input type="text" name="numFac" id="numFac" class="form-control form-control-sm" value="<?php echo $num_doc; ?>" required style="text-align: right;"></div>
                                                </div>
                                                <div class="col-2">
                                                    <div><input type="date" name="fechaDoc" id="fechaDoc" class="form-control form-control-sm" value="<?php echo $fecha_fact; ?>"></div>
                                                </div>
                                                <div class="col-2">
                                                    <div><input type="date" name="fechaVen" id="fechaVen" class="form-control form-control-sm" value="<?php echo $fecha_ven; ?>"></div>
                                                </div>
                                                <div class="col-2">
                                                    <div><span class="small"></span></div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><span class="small"></span></div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-center"><span class="small">VALOR:</span></div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-center"><span class="small">IVA:</span>
                                                    </div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-center"><span class="small">BASE:</span></div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-center"><span class="small"></span></div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><span class="small">VALOR FACTURA:</span></div>
                                                </div>
                                                <div class="col-2"><input type="text" name="valor_pagar" id="valor_pagar" value="<?php echo $valor_factura; ?>" class="form-control form-control-sm" style="text-align: right;" required onkeyup="valorMiles(id)"></div>
                                                <div class="col-2">
                                                    <input type="text" name="valor_iva" id="valor_iva" value="<?php echo $valor_iva; ?>" class="form-control form-control-sm" style="text-align: right;" onkeyup="valorMiles(id)" onchange="calculoValorBase();" ondblclick="calculoIva();" required>
                                                </div>
                                                <div class="col-2"><input type="text" name="valor_base" id="valor_base" value="<?php echo $valor_base; ?>" class="form-control form-control-sm" style="text-align: right;" onkeyup="valorMiles(id)"></div>
                                                <div class="col-2"><button type="button" id="bottonGuardarCxp" class="btn btn-danger btn-sm" onclick="procesaCausacionCxp('<?php echo $id_crp; ?>')">Guardar</button></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><span class="small">IMPUTACION:</span></div>
                                                </div>
                                                <div class="col-2"><input type="text" name="valor" id="valor" value="<?php echo $valor_causado; ?>" class="form-control form-control-sm" style="text-align: right;" required readonly></div>
                                                <div class=" col-2 text-left">
                                                    <a class="btn btn-outline-success btn-sm btn-circle shadow-gb" onclick="cargaRubrosRp('<?php echo $id_crp; ?>')"><span class="fas fa-plus fa-lg"></span></a>
                                                </div>
                                                <div class="col-2">
                                                </div>
                                            </div>

                                            <div class="row ">
                                                <div class="col-2">
                                                    <div class="col"><span class="small">CENTROS DE COSTOS:</span></div>
                                                </div>
                                                <div class="col-2"><input type="text" name="valor_costo" id="valor_costo" value="<?php echo $valor_costo; ?>" class="form-control form-control-sm" style="text-align: right;" required></div>
                                                <div class=" col-2"><a class="btn btn-outline-warning btn-sm btn-circle shadow-gb" onclick="cargaCentrosCosto('<?php echo $id_doc; ?>')"><span class="fas fa-eye fa-lg"></span></a>
                                                    <a class="btn btn-outline-primary btn-sm btn-circle shadow-gb" onclick="consultaCentrosCosto()"><span class="fas fa-hospital-user fa-lg"></span></a>
                                                    <a class="btn btn-outline-success btn-sm btn-circle shadow-gb" onclick="ajustarCausacionCostos('<?php echo $id_doc; ?>')"><span class="far fa-edit fa-lg"></span></a>
                                                </div>
                                            </div>
                                            <div class="row pb-2">
                                                <div class="col-2">
                                                    <div class="col"><span class="small">DESCUENTOS:</span></div>
                                                </div>
                                                <div class="col-2"><input type="text" name="descuentos" id="descuentos" class="form-control form-control-sm" style="text-align: right;" value="<?php echo $valor_ret; ?>" required onkeyup="valorMiles(id)"></div>
                                                <div class="col-8"><a class="btn btn-outline-primary btn-sm btn-circle shadow-gb" onclick="cargaDescuentos('<?php echo $id_doc; ?>')"><span class="fas fa-minus fa-lg"></span></a></div>
                                            </div>
                                            <div class="row ">
                                                <div class="col-2">
                                                    <div><span class="small"></span></div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-align: center">
                                                        <button type="button" class="btn btn-primary btn-sm" onclick="generaMovimientoCxp();">Generar movimiento</button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                                <br>
                                <input type="hidden" id="id_ctb_doc" name="id_ctb_doc" value="<?php echo $id_doc; ?>">
                                <table id="tableMvtoContableDetalle" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead class="text-center">
                                        <tr>
                                            <th style="width: 35%;">Cuenta</th>
                                            <th style="width: 35%;">Tercero</th>
                                            <th style="width: 10%;">Debito</th>
                                            <th style="width: 10%;">Credito</th>
                                            <th style="width: 10%;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modificartableMvtoContableDetalle">
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
                                                <button text="0" class="btn btn-primary btn-sm" onclick="GestMvtoDetalle(this)">Agregar</button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="text-center pt-4">
                        <a type="button" class="btn btn-primary btn-sm" onclick="imprimirFormatoDoc(<?php echo $id_doc; ?>);" style="width: 5rem;"> <span class="fas fa-print "></span></a>
                        <a onclick="terminarDetalle('<?php echo $tipo_dato; ?>')" class="btn btn-danger btn-sm" style="width: 7rem;" href="#"> Terminar</a>
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