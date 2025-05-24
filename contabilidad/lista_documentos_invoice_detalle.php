<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include_once '../conexion.php';
include_once '../permisos.php';
include_once '../financiero/consultas.php';
include_once '../terceros.php';
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../head.php';
// Consulta tipo de presupuesto
$id_rad = isset($_POST['id_rad']) ? $_POST['id_rad'] : 0;
$id_doc = isset($_POST['id_doc']) ? $_POST['id_doc'] : 0;
$tipo_dato  = $_POST['tipo_dato'];
$id_vigencia = $_SESSION['id_vigencia'];

$datosCrp = [];
function pesos($valor)
{
    return '$ ' . number_format($valor, 2, '.', ',');
}
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$fecha_cierre = fechaCierre($_SESSION['vigencia'], 55, $cmd);

try {
    $sql = "SELECT `id_doc_fuente` FROM `ctb_fuente` WHERE `cod` = '$tipo_dato'";
    $rs = $cmd->query($sql);
    $fuente = $rs->fetch();
    $tipo_dato = $fuente['id_doc_fuente'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

if ($id_doc == 0) {
    $fecha_doc = date('Y-m-d');
    try {
        $sql = "SELECT
                    `pto_rad`.`id_pto_rad` AS `id_crp`
                    , `pto_rad`.`id_tercero_api` AS `id_tercero`
                    , `pto_rad`.`fecha`
                    , `pto_rad`.`fecha` AS `fecha_crp`
                    , `pto_rad`.`objeto` AS `detalle`
                    , 'FACTURA' AS `fuente`
                    , 0 AS `estado`
                    , 0 AS `val_factura`
                    , 0 AS `val_imputacion`
                    , 0 AS `val_ccosto`
                    , 0 AS `val_retencion`
                    , 0 AS `id_ref_ctb`
                    , $id_rad AS `id_rad`
                FROM
                    `pto_rad`
                WHERE (`pto_rad`.`id_pto_rad` = $id_rad) LIMIT 1";
        $rs = $cmd->query($sql);
        $datosDoc = $rs->fetch();
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
} else {
    $datosDoc = GetValoresCxP($id_doc, $cmd);
    $id_manu = $datosDoc['id_manu'];
    $fecha_doc = $datosDoc['fecha'];
    $fecha_doc = date("Y-m-d", strtotime($fecha_doc));
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

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_ctb_referencia`,`nombre` FROM `ctb_referencia` WHERE `accion` = 1";
    $rs = $cmd->query($sql);
    $referencias = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$fecha = date('Y-m-d', strtotime($datosDoc['fecha']));

// Consulto tercero registrado en contratación del api de tercero para mostrar el nombre
// Consulta terceros en la api ********************************************* API
$tercero = '---';
if (!empty($datosDoc) && $datosDoc['id_tercero'] > 0) {
    $terceros = getTerceros($datosDoc['id_tercero'], $cmd);
    $tercero = ltrim($terceros[0]['nom_tercero']);
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
                                        <form id="formMvtoCtbInvoice">
                                            <input type="hidden" id="fec_cierre" name="fec_cierre" value="<?php echo $fecha_cierre; ?>">
                                            <div class="row mb-1">
                                                <div class="col-2">
                                                    <div class="col"><span class="small">NUMERO ACTO:</span></div>
                                                </div>
                                                <div class="col-10">
                                                    <input type="number" name="numDoc" id="numDoc" class="form-control form-control-sm" value="<?php echo $id_manu; ?>" required>
                                                    <input type="hidden" id="id_doc_fuente" name="id_doc_fuente" value="<?php echo $tipo_dato; ?>">
                                                    <input type="hidden" id="id_rad" name="id_rad" value="<?php echo $datosDoc['id_rad'] > 0 ? $datosDoc['id_rad'] : 0 ?>">
                                                </div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-2">
                                                    <div class="col"><span class="small">FECHA:</span></div>
                                                </div>
                                                <div class="col-10">
                                                    <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" value="<?php echo $fecha_doc; ?>" min="<?= date('Y-m-d', strtotime($datosDoc['fecha_crp'])) ?>" max="<?= $_SESSION['vigencia'] . '-12-31' ?>" required>
                                                </div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-2">
                                                    <div class="col"><span class="small">REFERENCIA:</span></div>
                                                </div>
                                                <div class="col-10">
                                                    <select id="slcReferencia" name="slcReferencia" class="form-control form-control-sm">
                                                        <option value="0">Seleccione...</option>
                                                        <?php
                                                        foreach ($referencias as $rf) {
                                                            $slc = $datosDoc['id_ref_ctb'] == $rf['id_ctb_referencia'] ? 'selected' : '';
                                                            echo "<option value='{$rf['id_ctb_referencia']}' {$slc}>{$rf['nombre']}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
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
                                                    <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-span="Default select example" rows="3" required="required"><?= $datosDoc['detalle']; ?></textarea>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="text-center py-2">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="generaMovimientoInvoice(this);" <?php echo $datosDoc['estado'] == '1' ? '' : 'disabled' ?>>Generar movimiento</button>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="" <?php echo $datosDoc['estado'] == '2' ? 'disabled' : '' ?> id="btnGuardaMvtoCtbInvoice" text="<?= $id_doc ?>">Guardar</button>
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
                                                <input type="hidden" name="tipodato" id="tipodato" value="<?= $tipo_dato; ?>">
                                            </td>
                                            <td>
                                                <?php
                                                if ($tipo_dato == '1') {
                                                    $trc = $tercero;
                                                    $idter = $datosDoc['id_tercero'];
                                                } else {
                                                    $trc = '';
                                                    $idter = 0;
                                                }
                                                ?>
                                                <input type="text" name="bTercero" id="bTercero" class="form-control form-control-sm bTercero" required value="<?= $trc; ?>">
                                                <input type="hidden" name="idTercero" id="idTercero" value="<?= $idter; ?>">
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
                        <a onclick="terminarDetalleInvoice('<?= 'FELE'; ?>')" class="btn btn-danger btn-sm" style="width: 7rem;" href="#"> Terminar</a>
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