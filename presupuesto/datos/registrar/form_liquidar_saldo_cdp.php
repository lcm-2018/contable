<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../../../conexion.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$_post = json_decode(file_get_contents('php://input'), true);
$cdp = $_post['id'];
try {
    $sql = "SELECT id,fecha FROM tb_fin_fecha WHERE vigencia = '2023'  ";
    $res = $cmd->query($sql);
    $row = $res->fetch();
    if (!$row) {
        $fecha = date("Y-m-d");
        $respuesta = 0;
    } else {
        $fecha = date('Y-m-d', strtotime($row['fecha']));
        $respuesta = 1;
    }
    // cerrar conexion con base de datos

} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    // seleccionar la fecha minima de tb_fin_periodos cuando vigencia es igual a $vigencia
    $sql = "SELECT min(fecha_cierre) as fecha_cierre FROM tb_fin_periodos WHERE vigencia = '2022'";
    $res = $cmd->query($sql);
    $datos = $res->fetch();
    $fecha_cierre = date('Y-m-d', strtotime($datos['fecha_cierre']));
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $sql = "SELECT
    `pto_documento`.`id_manu` 
    , `pto_documento`.`fecha`
    , `pto_documento_detalles`.`rubro`
    , `pto_documento_detalles`.`valor`
    , `pto_documento_detalles`.`id_documento`
    , `pto_documento_detalles`.`id_detalle`
    FROM
    `pto_documento_detalles`
    INNER JOIN `pto_documento` 
        ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
     WHERE (`pto_documento_detalles`.`id_documento` ='$cdp');";
    $res = $cmd->query($sql);
    $cdps = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
$url = $_SESSION['urlin'];
date_default_timezone_set('America/Bogota');
$fecha = date("Y-m-d");
?>
<div class="px-0">
    <form id="modLiberaCdp2">
        <div class="shadow mb-3">
            <div class="card-header" style="background-color: #16a085 !important;">
                <h6 style="color: white;"><i class="fas fa-lock fa-lg" style="color: #FCF3CF"></i>&nbsp;VALORES A LIQUIDAR DE CDP <?php echo $cdp; ?></h5>
                    <input type="hidden" id="id_cdp_doc" name="id_cdp_doc" value="<?php echo $cdp; ?>">
                    <input type="text" id="id_doc_neo" name="id_doc_neo" value="<?php echo ''; ?>">
            </div>
            <div class="pt-3 px-3">
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">FECHA: </label></div>
                    </div>
                    <div class="col-3">
                        <div class="col"> <input type="date" class="form-control form-control-sm" id="fecha" name="fecha" required value="<?php echo $fecha; ?>" min="<?php echo $fecha_cierre; ?>" max="<?php echo $fecha_max; ?>"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">CONCEPTO: </label></div>
                    </div>
                    <div class="col-8">
                        <div class="col"> <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3" required="required"></textarea></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small"> </label></div>
                    </div>
                    <div class="col-3">
                        <div class="col-2"><button type="button" class="btn btn-danger btn-sm " onclick="EnviarLiquidarCdp('')">Guardar</button></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table id="tableListaCdp" class="table table-striped table-bordered  table-sm table-hover " style="width: 97%;">
                            <thead>
                                <tr>
                                    <th style="width: 20%">Numero CDP</th>
                                    <th style="width: 20%">Fecha</th>
                                    <th style="width: 20%">Rubro</th>
                                    <th style="width: 15%">Valor</th>
                                    <th style="width: 15%">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total = 0;
                                $saldo_total = 0;
                                $j = 1;

                                foreach ($cdps as $lp) {
                                    $id_cdp = $lp['id_pto_doc'];
                                    $id_cdp_mvto = $lp['id_pto_mvto'];
                                    $dato = $id_cdp_mvto . "_" . $j;
                                    $liquidar = '<a value="' . $id_cdp . '" onclick="CargarFormularioLiquidar(' . $id_cdp . ')" class="text-blue " role="button" title="Detalles"><span>Liquidar saldo</span></a>';
                                    // Consultar el valor registrado por cada rubro
                                    try {
                                        $sql = "SELECT sum(valor) as registrado FROM pto_documento_detalles WHERE id_auto_dep = '$lp[id_pto_doc]' AND rubro ='$lp[rubro]' AND (tipo_mov ='CRP' OR tipo_mov ='LRP')";
                                        $res = $cmd->query($sql);
                                        $registrado = $res->fetch(PDO::FETCH_ASSOC);
                                        $saldo = $lp['valor'] - $registrado['registrado'];
                                    } catch (PDOException $e) {
                                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                                    }
                                    // Consulto el valor liberado por cada rubro
                                    try {
                                        $sql = "SELECT sum(valor) as liberado FROM pto_documento_detalles WHERE id_auto_dep = '$lp[id_pto_doc]' AND rubro ='$lp[rubro]' AND tipo_mov ='LCD'";
                                        $res = $cmd->query($sql);
                                        $valor_lib = $res->fetch(PDO::FETCH_ASSOC);
                                        //$saldo = $saldo + $valor_lib['liberado'];
                                    } catch (PDOException $e) {
                                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                                    }
                                    $saldo2 = number_format($saldo, 2, '.', ',');
                                    $campo = '<div class="btn-group">
                                                 <input type="text" class="form-control form-control-sm text-right" id="valor' . $j . '" name="valor' . $j . '" required value="' . $saldo2 . '" min="0" max="' . $saldo . '" onkeyup="valorMiles()">
                                                  <button type="button" class="btn btn-primary btn-sm"  onclick="registrarLiquidacionDetalle(\'' . $dato . '\')">-</button>
                                            </div>
                                    ';
                                    echo '<tr class="row-success">';
                                    echo '<td>' . $lp['id_manu'] . '</td>';
                                    echo '<td>' . date('Y-m-d', strtotime($lp['fecha'])) . '</td>';
                                    echo '<td class="text-left">' . $lp['rubro'] . '</td>';
                                    echo '<td class="text-right">'  . number_format($lp['valor'], 2, '.', ',') . '</td>';
                                    echo '<td class="text-right">' . $campo  . '</td>';
                                    echo '</tr>';
                                    $j++;
                                    $saldo = 0;
                                    $saldo2 = 0;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </form>
    <div class="text-right">
        <a class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
    </div>
    </form>
</div>