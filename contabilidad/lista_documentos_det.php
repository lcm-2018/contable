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
$id_crp = $_POST['id_crp'] ?? 0;
$tipo_dato = $_POST['tipo_dato'] ?? $_POST['id_soporte'];
$tipo_mov = $_POST['tipo_mov'] ?? '0';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

// Variables que no llegan de presupuesto
$num_doc = '';
$valor_causado = '';
$dif = 0;
$debito = 0;
$credito = 0;
$valor_iva = '';
$valor_base  = '';
$id_manu = '';
$valor_costo = '';
$valor_ret = '';
$valor_factura = '';
$fecha_doc = date('Y-m-d');
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 5, $cmd);
$fecha = fechaSesion($_SESSION['vigencia'], $_SESSION['id_user'], $cmd);
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
$fecha_fact = $fecha;
$fecha_ven = strtotime('+30 day', strtotime($fecha_doc));
$fecha_ven = date('Y-m-d', $fecha_ven); // fecha final con 30 dias sumados

if (isset($_POST['id_doc'])) {
    try {
        $sql = "SELECT id_ctb_doc,fecha,id_manu,detalle,id_tercero,tipo_doc FROM ctb_doc WHERE id_ctb_doc=$id_doc";
        $rs = $cmd->query($sql);
        $datosMov = $rs->fetch();
        $tipo_dato2 = $datosMov['tipo_doc'];
        $fecha_doc = $datosMov['fecha'];
        $fecha_doc = date('Y-m-d', strtotime($fecha_doc));
        $objeto = $datosMov['detalle'];
        $id_ter = $datosMov['id_tercero'];
        $id_manu = $datosMov['id_manu'];
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
        $sql = "SELECT id_pto_doc FROM pto_documento_detalles WHERE id_ctb_doc=$id_doc";
        $rs = $cmd->query($sql);
        $datosCrp = $rs->fetch();
        $id_crp = $datosCrp['id_pto_doc'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    // Consulto el valor de causacion de costo asociado al registro en la tabla seg_ctb_causa_costos
    try {
        $sql = "SELECT sum(valor) as valor FROM seg_ctb_causa_costos WHERE id_ctb_doc=$id_doc AND estado=0 GROUP BY id_ctb_doc";
        $rs = $cmd->query($sql);
        $sumaCosto = $rs->fetch();
        $valor_costo = number_format($sumaCosto['valor'], 2, '.', ',');
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    // Consulto el valor del RP pendiente para causar en el movimiento contable
    /*
    try {
        $sql = "SELECT sum(valor) as valor FROM pto_documento_detalles WHERE id_pto_doc=$id_crp GROUP BY id_pto_doc";
        $rs = $cmd->query($sql);
        $sumaCrp = $rs->fetch();
        $valor_obl = $sumaCrp['valor'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    */
    // Consulto la causacion de retenciones asociadas al registro en la tabla seg_ctb_retenciones
    try {
        $sql = "SELECT sum(valor_retencion) as valor FROM seg_ctb_causa_retencion WHERE id_ctb_doc=$id_doc  GROUP BY id_ctb_doc";
        $rs = $cmd->query($sql);
        $sumaRet = $rs->fetch();
        $valor_ret = number_format($sumaRet['valor'], 2, '.', ',');
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}

// Si la solicitud de causación esta asociada a un registro presupuestal consultamos los datos del registro
if (isset($_POST['id_crp'])) {
    try {
        $sql = "SELECT id_tercero, fecha, objeto FROM pto_documento WHERE id_pto_doc=$id_crp";
        $rs = $cmd->query($sql);
        $datosCrp = $rs->fetch();
        $id_ter = $datosCrp['id_tercero'];
        $fecha_doc = date('Y-m-d'); //, strtotime($datosCrp['fecha']));
        $objeto = $datosCrp['objeto'];
        $fecha_fact = $fecha_doc;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}


// consulto el valor causado valor en la tabla pto_documento_detalles
if (isset($_POST['id_doc'])) {
    try {
        $sql = "SELECT sum(valor) as valor FROM pto_documento_detalles WHERE id_ctb_doc=$id_doc AND tipo_mov='COP' GROUP BY id_pto_doc";
        $rs = $cmd->query($sql);
        $sumaCausado = $rs->fetch();
        $valor_causado = number_format($sumaCausado['valor'], 2, '.', ',');
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
// Consulto el tipo de documentos en seg_ctb_tipodoc
try {
    $sql = "SELECT id_ctb_tipodoc, tipo FROM seg_ctb_tipodoc ORDER BY tipo";
    $rs = $cmd->query($sql);
    $tipodoc = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto los datos de la factura que esten asociados seg_ctb_factura
$tipo_doc = '';
$detalle = '';
if (isset($_POST['id_doc'])) {
    try {
        $sql = "SELECT id_ctb_doc,tipo_doc,num_doc,fecha_fact,fecha_ven,valor_pago,valor_iva,valor_base,id_pto_crp,detalle FROM seg_ctb_factura WHERE id_ctb_doc=$id_doc";
        $consulta = $sql;
        $rs = $cmd->query($sql);
        $datosFactura = $rs->fetch();
        $tipo_doc = isset($datosFactura['tipo_doc']) ? $datosFactura['tipo_doc'] : '0';
        $num_doc = $datosFactura['num_doc'];
        $fecha_fact = date('Y-m-d', strtotime($datosFactura['fecha_fact']));
        $fecha_ven = date('Y-m-d', strtotime($datosFactura['fecha_ven']));
        $valor_factura = number_format($datosFactura['valor_pago'], 2, '.', ',');
        $valor_iva = number_format($datosFactura['valor_iva'], 2, '.', ',');
        $valor_base = number_format($datosFactura['valor_base'], 2, '.', ',');
        $id_crp = $datosFactura['id_pto_crp'];
        $detalle = $datosFactura['detalle'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
// Consulto tercero registrado en contratación del api de tercero para mostrar el nombre
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
                                    DETALLE DEL MOVIMIENTO CONTABLE <?php echo $tipo_dato; ?>
                                </div>
                            </div>
                        </div>
                        <!-- Formulario para nuevo reistro -->
                        <input type="hidden" id="peReg" value="<?php echo $permisos['registrar']; ?>">
                        <form id="formAddDetalleCtb">
                            <div class="card-body" id="divCuerpoPag">
                                <div>
                                    <div class="right-block">
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="numDoc" class="small">NUMERO ACTO:</label></div>
                                            </div>
                                            <div class="col-2"><input type="number" name="numDoc" id="numDoc" class="form-control form-control-sm" value="<?php echo $id_manu; ?>" required readonly>
                                                <input type="hidden" id="tipodato" name="tipodato" value="<?php echo $tipo_dato; ?>">
                                                <input type="hidden" id="id_crpp" name="id_crpp" value="<?php echo $id_crp; ?>">

                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">FECHA:</label></div>
                                            </div>
                                            <div class="col-2"> <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" min="<?php echo $fecha_doc; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_doc; ?>" onchange="buscarConsecutivoCont('NCXP');" readonly></div>
                                        </div>
                                        <?php
                                        if ($tipo_dato == 'NCXP') {
                                            $ver = 'readonly';
                                        } else {
                                            $ver = '';
                                        }
                                        ?>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">TERCERO:</label></div>
                                            </div>
                                            <div class="col-6"><input type="text" name="tercero" id="tercero" class="form-control form-control-sm" value="<?php echo $tercero; ?>" required>
                                                <input type="hidden" name="id_tercero" id="id_tercero" value="<?php echo $id_ter; ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">OBJETO:</label></div>
                                            </div>
                                            <div class="col-10"><textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3" required="required"><?php echo $objeto; ?></textarea></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">DETALLE:</label></div>
                                            </div>
                                            <div class="col-10"><textarea id="detalle" type="text" name="detalle" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="2"><?php echo $detalle; ?></textarea></div>
                                        </div>
                                        <?php
                                        if ($tipo_dato == 'NCXP') {
                                        ?>
                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><label for="numDoc" class="small"></label></div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-center"><label for="numDoc" class="small">Tipo documento:</label></div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-center"><label for="numDoc" class="small">Número de documento:</label>
                                                    </div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-center"><label for="numDoc" class="small text-center">Fecha factura:</label></div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-center"><label for="numDoc" class="small">Fecha vencimiento:</label></div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><label for="numDoc" class="small">DOCUMENTO:</label></div>
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
                                                    <div><label for="numDoc" class="small"></label></div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><label for="numDoc" class="small"></label></div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-center"><label for="numDoc" class="small">VALOR:</label></div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-center"><label for="numDoc" class="small">IVA:</label>
                                                    </div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-center"><label for="numDoc" class="small">BASE:</label></div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-center"><label for="numDoc" class="small"></label></div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-2">
                                                    <div class="col"><label for="fecha" class="small">VALOR FACTURA:</label></div>
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
                                                    <div class="col"><label for="fecha" class="small">IMPUTACION:</label></div>
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
                                                    <div class="col"><label for="fecha" class="small">CENTROS DE COSTOS:</label></div>
                                                </div>
                                                <div class="col-2"><input type="text" name="valor_costo" id="valor_costo" value="<?php echo $valor_costo; ?>" class="form-control form-control-sm" style="text-align: right;" required></div>
                                                <div class=" col-2"><a class="btn btn-outline-warning btn-sm btn-circle shadow-gb" onclick="cargaCentrosCosto('<?php echo $id_doc; ?>')"><span class="fas fa-eye fa-lg"></span></a>
                                                    <a class="btn btn-outline-primary btn-sm btn-circle shadow-gb" onclick="consultaCentrosCosto()"><span class="fas fa-hospital-user fa-lg"></span></a>
                                                    <a class="btn btn-outline-success btn-sm btn-circle shadow-gb" onclick="ajustarCausacionCostos('<?php echo $id_doc; ?>')"><span class="far fa-edit fa-lg"></span></a>
                                                </div>
                                            </div>
                                            <div class="row pb-2">
                                                <div class="col-2">
                                                    <div class="col"><label for="fecha" class="small">DESCUENTOS:</label></div>
                                                </div>
                                                <div class="col-2"><input type="text" name="descuentos" id="descuentos" class="form-control form-control-sm" style="text-align: right;" value="<?php echo $valor_ret; ?>" required onkeyup="valorMiles(id)"></div>
                                                <div class="col-8"><a class="btn btn-outline-primary btn-sm btn-circle shadow-gb" onclick="cargaDescuentos('<?php echo $id_doc; ?>')"><span class="fas fa-minus fa-lg"></span></a></div>
                                            </div>
                                            <div class="row ">
                                                <div class="col-2">
                                                    <div><label for="fecha" class="small"></label></div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-align: center">
                                                        <button type="button" class="btn btn-primary btn-sm" onclick="generaMovimientoCxp();">Generar movimiento</button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        }

                                        if ($tipo_dato != 'NCXP') {
                                        ?>

                                            <div class="row ">
                                                <div class="col-2">
                                                    <div><label for="fecha" class="small"></label></div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="text-align: center">
                                                        <button type="button" id="bottonGuardarCxp" class="btn btn-danger btn-sm" onclick="procesaCausacionCxp('<?php echo $id_crp; ?>')">Guardar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
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
                    </tfoot>
                    <input type="hidden" id="valor_dif" name="valor_dif" value="<?php echo $dif; ?>">
                    </table>
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
    <!-- Script -->
    <script>
        window.onload = function() {
            buscarConsecutivoCont('NCXP');
        }
    </script>

</body>

</html>