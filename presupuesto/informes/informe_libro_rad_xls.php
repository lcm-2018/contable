<?php
session_start();
set_time_limit(5600);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$vigencia = $_SESSION['vigencia'];
$fecha_corte = $_POST['fecha_corte'];
$fecha_ini = $_POST['fecha_ini'];

function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../terceros.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
//
try {
    $sql = "SELECT
                `pto_rad`.`fecha`
                , `pto_rad`.`estado`
                , `pto_rad_detalle`.`id_rubro`
                , `pto_rad`.`id_manu`
                , `pto_rad`.`id_pto_rad`
                , `pto_rad`.`objeto`
                , `pto_rad`.`num_factura`
                , `pto_rad_detalle`.`id_tercero_api`
                , `pto_cargue`.`cod_pptal` AS `rubro`
                , `pto_cargue`.`nom_rubro`
                , IFNULL(`t1`.`valor`,0) AS `valor`
            FROM
                `pto_rad_detalle`
                INNER JOIN `pto_rad` 
                    ON (`pto_rad_detalle`.`id_pto_rad` = `pto_rad`.`id_pto_rad`)
                INNER JOIN `pto_cargue` 
                    ON (`pto_rad_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                (SELECT
                    `pto_rad_detalle`.`id_rubro`
                    , `pto_rad`.`id_pto_rad`
                    , SUM(IFNULL(`pto_rad_detalle`.`valor`,0)) - SUM(IFNULL(`pto_rad_detalle`.`valor_liberado`,0)) AS `valor`
                FROM
                    `pto_rad_detalle`
                    INNER JOIN `pto_rad` 
                        ON (`pto_rad_detalle`.`id_pto_rad` = `pto_rad`.`id_pto_rad`)
                    INNER JOIN `pto_cargue` 
                        ON (`pto_rad_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                WHERE (`pto_rad`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_rad`.`estado` <> 0)
                GROUP BY `pto_rad_detalle`.`id_rubro`, `pto_rad`.`id_pto_rad`) AS `t1`
                ON (`t1`.`id_rubro` = `pto_rad_detalle`.`id_rubro` AND `t1`.`id_pto_rad` = `pto_rad`.`id_pto_rad`)
            WHERE (`pto_rad`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_rad`.`estado` <> 0)
            ORDER BY `pto_rad`.`fecha`";
    $res = $cmd->query($sql);
    $causaciones = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$terceros = [];
if (!empty($causaciones)) {
    $id_t = [];
    foreach ($causaciones as $ca) {
        if ($ca['id_tercero_api'] != '') {
            $id_t[] = $ca['id_tercero_api'];
        }
    }
    $ids = implode(',', $id_t);
    $terceros = getTerceros($ids, $cmd);
}
$nom_informe = "RELACION DE RECONOCIMIENTOS";
include_once '../../financiero/encabezado_empresa.php';
?>
<table class="table-hover" style="width:100% !important; border-collapse: collapse;" border="1">
    <thead>
        <tr class="centrar">
            <th>No reconocimiento</th>
            <th>No factura</th>
            <th>Fecha</th>
            <th>Tercero</th>
            <th>CC/NIT</th>
            <th>Objeto</th>
            <th>Rubro</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (!empty($causaciones)) {
            foreach ($causaciones as $rp) {
                $key = array_search($rp['id_tercero_api'], array_column($terceros, 'id_tercero_api'));
                $tercero = $key !== false ? ltrim($terceros[$key]['nom_tercero']) : '---';
                $ccnit = $key !== false ? number_format($terceros[$key]['nit_tercero'], 0, "", ".") : '---';

                $fecha = date('Y-m-d', strtotime($rp['fecha']));
                if ($rp['valor'] >= 0) {
                    echo "<tr>
                        <td style='text-align:left'>" . $rp['id_manu'] . "</td>
                        <td style='text-align:left'>" . $rp['num_factura'] . "</td>
                        <td style='text-align:left;white-space: nowrap;'>" .   $fecha   . "</td>
                        <td style='text-align:left'>" .  $tercero . "</td>
                        <td style='text-align:right;white-space: nowrap;'>" .  $ccnit . "</td>
                        <td style='text-align:left'>" . $rp['objeto'] . "</td>
                        <td style='text-align:left'>" .  $rp['rubro'] . "</td>
                        <td style='text-align:right'>" . number_format($rp['valor'], 2, ".", ",")  . "</td>
                    </tr>";
                }
            }
        } else {
            echo "<tr><td colspan='8'  style='text-align:center'>No hay datos para mostrar</td></tr>";
        }
        ?>
    </tbody>
</table>