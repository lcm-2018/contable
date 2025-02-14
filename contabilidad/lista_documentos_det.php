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
$id_doc = isset($_POST['id_doc']) ? $_POST['id_doc'] : 0;
$id_crp = isset($_POST['id_crp']) ? $_POST['id_crp'] : 0;
$tipo_dato = $_POST['tipo_dato'];
$id_vigencia = $_SESSION['id_vigencia'];

$datosCrp = [];
function pesos($valor)
{
    return '$ ' . number_format($valor, 2, '.', ',');
}
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
if ($id_doc == 0) {
    try {
        $sql = "SELECT
                    `pto_crp`.`id_pto_crp` AS `id_crp`
                    , `pto_crp`.`id_tercero_api` AS `id_tercero`
                    , `pto_crp`.`fecha`
                    , `pto_crp`.`fecha` AS `fecha_crp`
                    , `pto_crp`.`objeto` AS `detalle`
                    , 'CUENTAS POR PAGAR' AS `fuente`
                    , 0 AS `estado`
                    , 0 AS `val_factura`
                    , 0 AS `val_imputacion`
                    , 0 AS `val_ccosto`
                    , 0 AS `val_retencion`
                FROM
                    `pto_crp`
                WHERE (`pto_crp`.`id_pto_crp` = $id_crp) LIMIT 1";
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
                                        <form id="formGetMvtoCtb">
                                            <div class="row mb-1">
                                                <div class="col-2">
                                                    <div class="col"><span class="small">NUMERO ACTO:</span></div>
                                                </div>
                                                <div class="col-10">
                                                    <input type="number" name="numDoc" id="numDoc" class="form-control form-control-sm" value="<?php echo $id_manu; ?>" required>
                                                    <input type="hidden" id="tipodato" name="tipodato" value="<?php echo $tipo_dato; ?>">
                                                    <input type="hidden" id="id_crpp" name="id_crpp" value="<?php echo $datosDoc['id_crp'] > 0 ? $datosDoc['id_crp'] : 0 ?>">

                                                </div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-2">
                                                    <div class="col"><span class="small">FECHA:</span></div>
                                                </div>
                                                <div class="col-10">
                                                    <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" value="<?php echo date('Y-m-d'); ?>" min="<?= date('Y-m-d', strtotime($datosDoc['fecha_crp'])) ?>" max="<?= $_SESSION['vigencia'] . '-12-31' ?>" required>
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
                                                    <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-span="Default select example" rows="3" required="required"><?= substr($datosDoc['detalle'], 0, 200); ?></textarea>
                                                </div>
                                            </div>
                                        </form>
                                        <?php
                                        if ($tipo_dato == '3') {
                                        ?>
                                            <div class="input-group input-group-sm mb-1 mt-3">
                                                <div class="input-group-prepend col-2 pr-0">
                                                    <button class="btn btn-outline-success btn-block text-left" type="button" onclick="FacturarCtasPorPagar('<?php echo $id_doc; ?>')" <?php echo $datosDoc['estado'] == '1' ? '' : 'disabled' ?>><i class="fas fa-file-invoice-dollar fa-lg mr-2"></i>Facturación</button>
                                                </div>
                                                <div class="form-control col-4" readonly id="valFactura"><?php echo pesos($datosDoc['val_factura']); ?></div>
                                            </div>
                                            <div class="input-group input-group-sm mb-1">
                                                <div class="input-group-prepend col-2 pr-0">
                                                    <button class="btn btn-outline-primary btn-block text-left" type="button" onclick="ImputacionCtasPorPagar('<?php echo $id_doc; ?>')" <?php echo $datosDoc['estado'] == '1' ? '' : 'disabled' ?>><i class="fas fa-file-signature fa-lg mr-2"></i>Imputación</button>
                                                </div>
                                                <div class="form-control col-4" readonly id="valImputacion"><?php echo pesos($datosDoc['val_imputacion']); ?></div>
                                            </div>
                                            <div class="input-group input-group-sm mb-1">
                                                <div class="input-group-prepend col-2 pr-0">
                                                    <button class="btn btn-outline-warning btn-block text-left" type="button" onclick="CentroCostoCtasPorPagar('<?php echo $id_doc; ?>')" <?php echo $datosDoc['estado'] == '1' ? '' : 'disabled' ?>><i class="fas fa-kaaba fa-lg mr-2"></i></i>Centro Costo</button>
                                                </div>
                                                <div class="col-4 input-group input-group-sm p-0">
                                                    <div class="form-control" readonly id="valCentroCosto"><?php echo pesos($datosDoc['val_ccosto']); ?></div>
                                                    <div class="input-group-append" title="Asignar centros de costo automaticamente">
                                                        <button class="btn btn-outline-warning" type="button" onclick="CausaAuCentroCostos(id)" <?= $datosDoc['estado'] == '1' ? '' : 'disabled' ?>><i class="fas fa-eject fa-lg"></i></button>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend col-2 pr-0">
                                                    <button class="btn btn-outline-info btn-block text-left" type="button" onclick="DesctosCtasPorPagar('<?php echo $id_doc; ?>')" <?php echo $datosDoc['estado'] == '1' ? '' : 'disabled' ?>><i class="fas fa-donate fa-lg mr-2"></i>Descuentos</button>
                                                </div>
                                                <div class="form-control col-4" readonly id="valDescuentos"><?php echo pesos($datosDoc['val_retencion']); ?></div>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    <div class="text-center py-2">
                                        <?php
                                        if ($tipo_dato == '3') {
                                        ?>
                                            <button type="button" class="btn btn-primary btn-sm" onclick="generaMovimientoCxp();" <?php echo $datosDoc['estado'] == '1' ? '' : 'disabled' ?>>Generar movimiento</button>
                                        <?php
                                        }
                                        ?>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="" <?php echo $datosDoc['estado'] == '2' ? 'disabled' : '' ?> id="GuardaDocCtb" text="<?= $id_doc ?>">Guardar</button>
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