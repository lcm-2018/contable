<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include_once '../conexion.php';
include_once '../permisos.php';
include_once '../financiero/consultas.php';
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../head.php';
// Consulta tipo de presupuesto
$id_doc = isset($_POST['id_doc']) ? $_POST['id_doc'] : 0;
$id_crp = isset($_POST['id_crp']) ? $_POST['id_crp'] : 0;
$tipo_dato = $_POST['tipo_dato'];
$id_vigencia = $_SESSION['id_vigencia'];

$datosCrp = [];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
if ($id_doc == 0) {
    try {
        $sql = "SELECT
                    `pto_crp`.`id_pto_crp`
                    , `pto_crp`.`id_tercero_api`
                    , `pto_crp`.`fecha`
                    , `pto_crp`.`objeto`
                FROM
                    `pto_crp`
                WHERE (`pto_crp`.`id_pto_crp` = $id_crp) LIMIT 1";
        $rs = $cmd->query($sql);
        $datosCrp = $rs->fetch();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
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
        $estado = 1;
        $id_tercero = $datosCrp['id_tercero_api'];
        $detalle = $datosCrp['objeto'];
        $iduser = $_SESSION['id_user'];
        $date = new DateTime('now', new DateTimeZone('America/Bogota'));
        $fecha = $date->format('Y-m-d');
        $fecha2 = $date->format('Y-m-d H:i:s');
        $query = "INSERT INTO `ctb_doc`
                        (`id_vigencia`,`id_tipo_doc`,`id_manu`,`id_tercero`,`fecha`,`detalle`,`estado`,`id_user_reg`,`fecha_reg`, `id_crp`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_vigencia, PDO::PARAM_INT);
        $query->bindParam(2, $tipo_dato, PDO::PARAM_INT);
        $query->bindParam(3, $id_manu, PDO::PARAM_INT);
        $query->bindParam(4, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(5, $fecha, PDO::PARAM_STR);
        $query->bindParam(6, $detalle, PDO::PARAM_STR);
        $query->bindParam(7, $estado, PDO::PARAM_INT);
        $query->bindParam(8, $iduser, PDO::PARAM_INT);
        $query->bindParam(9, $fecha2);
        $query->bindParam(10, $id_crp, PDO::PARAM_INT);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $id_doc = $cmd->lastInsertId();
        } else {
            echo $query->errorInfo()[2];
            exit();
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
try {
    $sql = "SELECT
                `ctb_fuente`.`nombre` AS `fuente`
                , `ctb_doc`.`id_ctb_doc`
                , `ctb_doc`.`fecha`
                , `ctb_doc`.`id_manu`
                , `ctb_doc`.`detalle`
                , `ctb_doc`.`id_tercero`
                , `ctb_doc`.`estado`
                , `ctb_doc`.`id_crp`
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

<body class="sb-nav-fixed <?php echo $_SESSION['navarlat'] == '1' ? 'sb-sidenav-toggled' : '' ?>">
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
                                                <input type="hidden" id="id_crpp" name="id_crpp" value="<?php echo $datosDoc['id_crp'] > 0 ? $datosDoc['id_crp'] : 0 ?>">

                                            </div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-2">
                                                <div class="col"><span class="small">FECHA:</span></div>
                                            </div>
                                            <div class="col-10"> <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" value="<?php echo date('Y-m-d', strtotime($datosDoc['fecha'])); ?>" readonly></div>
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
                                            <div class="col-10">
                                                <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-span="Default select example" rows="3" required="required" readonly><?php echo $datosDoc['detalle']; ?></textarea>
                                            </div>
                                        </div>
                                        <?php
                                        if ($tipo_dato == '3') {
                                        ?>
                                            <div class="btn-group btn-group-sm mt-2" role="group">
                                                <button type="button" class="btn btn-outline-success" onclick="FacturarCtasPorPagar('<?php echo $id_doc; ?>')"><i class="fas fa-file-invoice-dollar fa-lg mr-2"></i>Facturación</button>
                                                <button type="button" class="btn btn-outline-primary" onclick="ImputacionCtasPorPagar('<?php echo $id_doc; ?>')"><i class="fas fa-file-signature fa-lg mr-2"></i>Imputación</button>
                                                <button type="button" class="btn btn-outline-warning" onclick="CentroCostoCtasPorPagar('<?php echo $id_doc; ?>')"><i class="fas fa-kaaba fa-lg mr-2"></i></i>Centro Costo</button>
                                                <button type="button" class="btn btn-outline-info" onclick="DesctosCtasPorPagar('<?php echo $id_doc; ?>')"><i class="fas fa-donate fa-lg mr-2"></i>Descuentos</button>
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