<?php
session_start();
set_time_limit(10000);
ini_set('memory_limit', '512M');
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
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
                DATE_FORMAT(`pto_rad`.`fecha`,'%Y-%m-%d') AS `fecha`
                , `pto_rad`.`id_manu`
                , `pto_rad`.`id_pto_rad`
                , `pto_rad`.`objeto`
                , `pto_rad`.`num_factura`
                , `pto_cargue`.`cod_pptal` AS `rubro`
                , `pto_cargue`.`nom_rubro`
                , `tt`.`id_tercero_api`
                , `tt`.`id_rubro`
                , `tt`.`val1`
                , `tt`.`val2`
                , `tt`.`valor`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
            FROM
                `pto_rad`
                INNER JOIN
                    (SELECT
                        `id_pto_rad`
                        , `id_rubro`
                        , IFNULL(`valor`,0) as `val1`
                        , IFNULL(`valor_liberado`,0) as `val2`
                        , SUM(IFNULL(`valor`,0) - IFNULL(`valor_liberado`,0)) AS `valor`
                        , `id_tercero_api`
                    FROM
                        `pto_rad_detalle`
                    GROUP BY `id_pto_rad`, `id_rubro`,`id_tercero_api`) AS `tt`
                    ON (`tt`.`id_pto_rad` = `pto_rad`.`id_pto_rad`)
                INNER JOIN `pto_cargue` 
                    ON (`tt`.`id_rubro` = `pto_cargue`.`id_cargue`)
                LEFT JOIN `tb_terceros`
                    ON (`tt`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`)
            WHERE (`pto_rad`.`estado` = 2 AND DATE_FORMAT(`pto_rad`.`fecha`,'%Y-%m-%d') BETWEEN '$fecha_ini' AND '$fecha_corte')
            ORDER BY `pto_rad`.`fecha` ASC, `pto_rad`.`id_manu` ASC";
    $res = $cmd->query($sql);
    $causaciones = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
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
            <th>Liberado</th>
            <th>Neto</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (!empty($causaciones)) {
            foreach ($causaciones as $rp) {
                if ($rp['valor'] >= 0) {
                    echo "<tr>
                        <td style='text-align:left'>" . $rp['id_manu'] . "</td>
                        <td style='text-align:left'>" . $rp['num_factura'] . "</td>
                        <td style='text-align:left;white-space: nowrap;'>" .   $rp['fecha']   . "</td>
                        <td style='text-align:left'>" .  $rp['nom_tercero'] . "</td>
                        <td style='text-align:right;white-space: nowrap;'>" .  number_format($rp['nit_tercero'], 0, "", ".") . "</td>
                        <td style='text-align:left'>" . $rp['objeto'] . "</td>
                        <td style='text-align:left'>" .  $rp['rubro'] . "</td>
                        <td style='text-align:right'>" . number_format($rp['val1'], 2, ".", ",")  . "</td>
                        <td style='text-align:right'>" . number_format($rp['val2'], 2, ".", ",")  . "</td>
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