<?php
session_start();
set_time_limit(5600);
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

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
//
try {
    $sql = "SELECT 
                `taux`.`id_pto_cdp`
                , `taux`.`fecha`
                , `taux`.`id_manu`
                , `taux`.`objeto`
                , `taux`.`id_rubro`
                , `taux`.`rubro`
                , `taux`.`nom_rubro`
                , IFNULL(`t1`.`valor`,0) AS `val_cdp`
                , IFNULL(`t2`.`valor`,0) AS `val_crp`
            FROM
                (SELECT
                    `pto_cdp`.`id_pto_cdp`
                    , `pto_cdp`.`fecha`
                    , `pto_cdp`.`id_manu`
                    , `pto_cdp`.`objeto`
                    , `pto_cdp_detalle`.`id_rubro`
                    , `pto_cargue`.`cod_pptal` AS `rubro`
                    , `pto_cargue`.`nom_rubro`
                FROM
                    `pto_cdp_detalle`
                    INNER JOIN `pto_cdp` 
                        ON (`pto_cdp_detalle`.`id_pto_cdp` = `pto_cdp`.`id_pto_cdp`)
                    INNER JOIN `pto_cargue` 
                        ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                    WHERE (`pto_cdp`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_cdp`.`estado` <> 0)) AS `taux`
                LEFT JOIN
                    (SELECT
                        `pto_cdp`.`id_pto_cdp`
                        , `id_rubro`
                        , SUM(IFNULL(`valor`,0)) - SUM(IFNULL(`valor_liberado`,0)) AS `valor`
                    FROM
                        `pto_cdp_detalle`
                        INNER JOIN `pto_cdp` 
                            ON (`pto_cdp_detalle`.`id_pto_cdp` = `pto_cdp`.`id_pto_cdp`)
                    WHERE (`pto_cdp`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_cdp`.`estado` <> 0)
                    GROUP BY `id_pto_cdp`, `id_rubro`) AS `t1`
                    ON (`t1`.`id_pto_cdp` = `taux`.`id_pto_cdp` AND `t1`.`id_rubro` = `taux`.`id_rubro`)
                LEFT JOIN
                    (SELECT
                        `pto_cdp_detalle`.`id_pto_cdp`
                        , `pto_cdp_detalle`.`id_rubro`
                        , SUM(IFNULL(`pto_crp_detalle`.`valor`,0)) - SUM(IFNULL(`pto_crp_detalle`.`valor_liberado`,0)) AS `valor`
                    FROM
                        `pto_crp_detalle`
                        INNER JOIN `pto_cdp_detalle` 
                            ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                        INNER JOIN `pto_crp` 
                            ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                    WHERE (`pto_crp`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_crp`.`estado` <> 0)
                    GROUP BY `pto_cdp_detalle`.`id_pto_cdp`, `pto_cdp_detalle`.`id_rubro`) AS `t2`
                    ON (`t2`.`id_pto_cdp` = `taux`.`id_pto_cdp` AND `t2`.`id_rubro` = `taux`.`id_rubro`)
            GROUP BY `taux`.`id_pto_cdp`,`taux`.`id_rubro`
            ORDER BY `taux`.`fecha` ASC";
    $res = $cmd->query($sql);
    $causaciones = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$nom_informe = "RELACION DE CERTIFICADOS DE DISPONIBILIDAD PRESUPUESTAL";
include_once '../../financiero/encabezado_empresa.php';

?>

<table class="table-hover" style="width:100% !important; border-collapse: collapse;" border="1">
    <thead>
        <tr class="centrar">
            <th>CDP</th>
            <th>Fecha</th>
            <th>Objeto</th>
            <th>Rubro</th>
            <th>Nombre rubro</th>
            <th>Valor</th>
            <th>Saldo</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($causaciones as $rp) {
            $fecha = date("Y-m-d", strtotime($rp['fecha']));
            $valor_cdp = $rp['val_cdp'];
            $saldo = $rp['val_cdp'] - $rp['val_crp'];
            if ($valor_cdp > 0) {
                echo "<tr>";
                echo "<td>" . $rp['id_manu'] . "</td>";
                echo "<td style='white-space: nowrap;'>" . $fecha . "</td>";
                echo "<td>" . $rp['objeto'] . "</td>";
                echo "<td>" . $rp['rubro'] . "</td>";
                echo "<td>" . $rp['nom_rubro'] . "</td>";
                echo "<td style='text-align:right;'>" . pesos($valor_cdp) . "</td>";
                echo "<td style='text-align:right;'>" . pesos($saldo) . "</td>";
                echo "</tr>";
            }
        }
        ?>
    </tbody>
</table>