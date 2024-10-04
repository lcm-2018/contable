<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../financiero/consultas.php';

$_post = json_decode(file_get_contents('php://input'), true);
$cdp = $_post['id'];
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT
                `pto_documento`.`id_manu` 
                , `pto_documento`.`fecha`
                , `pto_documento_detalles`.`rubro`
                , `pto_documento_detalles`.`valor`
                , `pto_documento_detalles`.`id_documento`
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
// Consulta del valor registrado del cdp
try {
    $sql = "SELECT
    `pto_documento`.`id_manu`
    , `pto_documento`.`fecha`
    , SUM(`pto_documento_detalles`.`valor`) as valor
    , `pto_documento_detalles`.`id_documento`
    FROM
    `pto_documento_detalles`
    INNER JOIN `pto_documento` 
        ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
    WHERE (`pto_documento_detalles`.`id_auto_dep` ='$cdp' AND tipo_mov ='CRP')
    GROUP BY `pto_documento_detalles`.`id_documento`;";
    $res = $cmd->query($sql);
    $crp = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto el historial de liquidaciones del cdp
try {
    $sql = "SELECT
                `pto_documento`.`fecha`
                , `pto_documento_detalles`.`rubro`
                , `pto_documento_detalles`.`valor`
                , `pto_documento_detalles`.`id_detalle`
            FROM
                `pto_documento_detalles`
                INNER JOIN `pto_documento` 
                    ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
            WHERE (`pto_documento_detalles`.`id_auto_dep` =$cdp AND tipo_mov ='LCD');";
    $res = $cmd->query($sql);
    $liquidacion = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto el historial de liquidaciones del crp
try {
    $sql = "SELECT
                `pto_documento`.`fecha`
                , `pto_documento_detalles`.`rubro`
                , `pto_documento_detalles`.`valor`
                , `pto_documento_detalles`.`id_detalle`
            FROM
                `pto_documento_detalles`
                INNER JOIN `pto_documento` 
                    ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
            WHERE (`pto_documento_detalles`.`id_auto_dep` =$cdp AND tipo_mov ='LRP');";
    $res = $cmd->query($sql);
    $liquidacion_crp = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php';
// Consulta tipo de presupuesto
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">HISTORIAL DEL DOCUMENTO </h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-3">
            <div class="row">
                <div class="col-12">
                    <div class="col text-left"><label>CERTIFICADO DE DISPONIBILIDAD PRESUPUESTAL:</label></div>
                </div>
            </div>
            <table id="tableListaCdp" class="table table-striped table-bordered  table-sm table-hover " style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 15%">Numero CDP</th>
                        <th style="width: 15%">Fecha</th>
                        <th style="width: 20%">Rubro</th>
                        <th style="width: 15%">Valor</th>
                        <th style="width: 20%">Saldo</th>
                        <th style="width: 20%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    $saldo_total = 0;
                    $j = 1;
                    $valores = [];
                    foreach ($cdps as $lp) {
                        $id_cdp = $lp['id_pto_doc'];
                        $liquidar = '<a value="' . $id_cdp . '" onclick="CargarFormularioLiquidar(' . $id_cdp . ')" class="text-blue " role="button" title="Detalles"><span>Liquidar saldo</span></a>';
                        // Consultar el valor registrado por cada rubro
                        try {
                            $sql = "SELECT sum(valor) as registrado FROM pto_documento_detalles WHERE id_auto_dep = '$lp[id_pto_doc]' AND rubro ='$lp[rubro]' AND (tipo_mov ='CRP' OR tipo_mov='LRP')";
                            $res = $cmd->query($sql);
                            $registrado = $res->fetch(PDO::FETCH_ASSOC);
                            $saldo = $lp['valor'] - $registrado['registrado'];
                        } catch (PDOException $e) {
                            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                        }
                        echo '<tr class="row-success">';
                        echo '<td>' . $lp['id_manu'] . '</td>';
                        echo '<td>' . date('Y-m-d', strtotime($lp['fecha'])) . '</td>';
                        echo '<td class="text-left">' . $lp['rubro'] . '</td>';
                        echo '<td class="text-right">'  . number_format($lp['valor'], 2, '.', ',') . '</td>';
                        echo '<td class="text-right">' . number_format($saldo, 2, '.', ',') . '</td>';
                        echo '<td>' . '' . '</td>';
                        echo '</tr>';
                        $total = $total + $lp['valor'];
                        $saldo_total = $saldo_total + $saldo;
                        $valor[$j] = $saldo;
                        $j++;
                    }
                    $j = 1;
                    foreach ($liquidacion as $liq) {
                        $id_liberacion = $liq['id_pto_mvto'];
                        $borrar = '<a  onclick="eliminarLiberacion(' . $id_liberacion . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb editar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
                        $saldo = $valor[$j] + $liq['valor'];
                        echo '<tr class="row-success" id="' . $id_liberacion . '">';
                        echo '<td>' . $lp['id_manu'] . '</td>';
                        echo '<td>' . date('Y-m-d', strtotime($liq['fecha'])) . '</td>';
                        echo '<td class="text-left">' . $liq['rubro'] . '</td>';
                        echo '<td class="text-right">'  . number_format($liq['valor'], 2, '.', ',') . '</td>';
                        echo '<td class="text-right">' . number_format($saldo, 2, '.', ',') . '</td>';
                        echo '<td>' . $borrar  . '</td>';
                        echo '</tr>';
                        $j++;
                        $total = $total + $liq['valor'];
                        $saldo_total = $saldo_total +  $liq['valor'];
                    }
                    if ($saldo_total == 0) {
                        $liquidar = null;
                    }
                    echo '<tr class="row-success">';
                    echo '<td colspan="3" class="text-left">&nbsp;Total</td>';
                    echo '<td class="text-right">' . number_format($total, 2, '.', ',') . '</td>';
                    echo '<td class="text-right">' .  number_format($saldo_total, 2, '.', ',') . '</td>';
                    echo '<td class="text-center">' .  $liquidar . '</td>';
                    ?>
                </tbody>
            </table>
            <div class="row">
                <div class="col-12">
                    <div class="col text-left"><label>CERTIFICADO DE REGISTRO PRESUPUESTAL:</label></div>
                </div>
            </div>
            <table id="tableListaCrp" class="table table-striped table-bordered  table-sm table-hover " style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 16%">Numero CRP</th>
                        <th style="width: 16%">Fecha</th>
                        <th style="width: 12%">Valor</th>
                        <th style="width: 12%">Causado</th>
                        <th style="width: 12%">Liquidado</th>
                        <th style="width: 12%">Saldo</th>
                        <th style="width: 20%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_rp = 0;
                    foreach ($crp as $lp) {
                        $id_crp = $lp['id_pto_doc'];
                        // Consultar el valor causado por cada rubro
                        try {
                            $sql = "SELECT
                                            SUM(`pto_documento_detalles`.`valor`) as val_causado
                                        , `pto_documento_detalles`.`id_documento`
                                    FROM
                                        `pto_documento_detalles`
                                        INNER JOIN `pto_documento` 
                                            ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
                                    WHERE `pto_documento_detalles`.`tipo_mov` ='COP' AND `pto_documento_detalles`.`id_auto_dep` ={$lp['id_pto_doc']} AND `pto_documento_detalles`.`estado` =0
                                    GROUP BY `pto_documento_detalles`.`id_documento`;";
                            $res = $cmd->query($sql);
                            $causado = $res->fetch(PDO::FETCH_ASSOC);
                            $saldo_rp = $lp['valor'] - $causado['val_causado'];
                            $sql2 = $sql;
                        } catch (PDOException $e) {
                            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                        }
                        // Consultar el valor liquidado por cada rubro
                        try {
                            $sql = "SELECT
                                        SUM(`valor`) AS val_liquidado
                                    FROM
                                        `pto_documento_detalles`
                                    WHERE `tipo_mov` ='LRP'
                                        AND `id_auto_crp` ={$lp['id_pto_doc']};";
                            $res = $cmd->query($sql);
                            $liquidado = $res->fetch(PDO::FETCH_ASSOC);
                            $valor_liquidado = $liquidado['val_liquidado'];
                            $saldo_rp = $saldo_rp + $valor_liquidado;
                        } catch (PDOException $e) {
                            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                        }
                        if ($saldo_rp > 0) {
                            $liquidarRp = '<a value="' . $id_cdp . '" onclick="CargarFormularioLiquidarCrp(' . $id_crp . ')" class="text-blue " role="button" title="Detalles"><span>Liquidar saldo</span></a>';
                        } else {
                            $liquidarRp = '';
                        }
                        echo '<tr class="row-success">';
                        echo '<td>' . $lp['id_manu'] . '</td>';
                        echo '<td>' . date('Y-m-d', strtotime($lp['fecha'])) . '</td>';
                        echo '<td class="text-right">'  . number_format($lp['valor'], 2, '.', ',') . '</td>';
                        echo '<td class="text-right">'  . number_format($causado['val_causado'], 2, '.', ',') . '</td>';
                        echo '<td class="text-right">'  . number_format($liquidado['val_liquidado'], 2, '.', ',') . '</td>';
                        echo '<td class="text-right">' . number_format($saldo_rp, 2, '.', ',') . '</td>';
                        echo '<td>' . $liquidarRp  . '</td>';
                        echo '</tr>';
                        $total_rp = $total_rp + $lp['valor'];
                    }
                    echo '<tr class="row-success">';
                    echo '<td colspan="3" class="text-left">&nbsp;Total</td>';
                    echo '<td class="text-right">' . number_format($total_rp, 2, '.', ',') . '</td>';
                    echo '<td class="text-right">' .  number_format($saldo_total, 2, '.', ',') . '</td>';
                    echo '<td class="text-right">' . '' . '</td>';
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-right pt-3">
        <a type="button" class="btn btn-danger btn-sm" data-dismiss="modal"> Cerrar</a>
    </div>

</div>
<?php
