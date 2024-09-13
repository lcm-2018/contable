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
                `taux`.`id_manu`
                , `taux`.`fecha`
                , `taux`.`detalle`
                , `taux`.`id_tercero_api`
                , `taux`.`rubro`
                , `taux`.`nom_rubro`
                , IFNULL(`t1`.`valor`,0) AS `valor_pag`
            FROM 	
                (SELECT
                    `ctb_doc`.`id_ctb_doc`
                    ,`ctb_doc`.`id_manu`
                    , `ctb_doc`.`fecha`
                    , `ctb_doc`.`detalle`
                    , `pto_pag_detalle`.`id_tercero_api`
                    , `pto_cdp_detalle`.`id_rubro`
                    , `pto_cargue`.`cod_pptal` AS `rubro`
                    , `pto_cargue`.`nom_rubro`
                FROM
                    `pto_pag_detalle`
                    INNER JOIN `ctb_doc` 
                    ON (`pto_pag_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    INNER JOIN `pto_cop_detalle` 
                    ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                    INNER JOIN `pto_crp_detalle` 
                    ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                    INNER JOIN `pto_cdp_detalle` 
                    ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                    INNER JOIN `pto_cargue` 
                    ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                WHERE (`ctb_doc`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `ctb_doc`.`estado` <> 0)) AS `taux`
                LEFT JOIN
                    (SELECT
                        `pto_cdp_detalle`.`id_rubro`
                        , `ctb_doc`.`id_ctb_doc`
                        , SUM(IFNULL(`pto_pag_detalle`.`valor`,0)) - SUM(IFNULL(`pto_pag_detalle`.`valor_liberado`,0)) AS `valor`
                    FROM
                        `pto_pag_detalle`
                        INNER JOIN `ctb_doc` 
                            ON (`pto_pag_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        INNER JOIN `pto_cop_detalle` 
                            ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                        INNER JOIN `pto_crp_detalle` 
                            ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                        INNER JOIN `pto_cdp_detalle` 
                            ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                        INNER JOIN `pto_cargue` 
                            ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                    WHERE (`ctb_doc`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `ctb_doc`.`estado` <> 0)
                    GROUP BY `pto_cdp_detalle`.`id_rubro`, `ctb_doc`.`id_ctb_doc`) AS `t1`
                    ON (`t1`.`id_rubro` = `taux`.`id_rubro` AND `t1`.`id_ctb_doc` = `taux`.`id_ctb_doc`)";
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
$nom_informe = "RELACION DE PAGOS";
include_once '../../financiero/encabezado_empresa.php';
?>
<table class="table-hover" style="width:100% !important; border-collapse: collapse;" border="1">
    <thead>
        <tr class="centrar">
            <th>No Egreso</th>
            <th>Fecha</th>
            <th>Tercero</th>
            <th>Cc/Nit</th>
            <th>Objeto</th>
            <th>Rubro</th>
            <th>Nombre rubro</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($causaciones as $rp) {
            $key = array_search($rp['id_tercero_api'], array_column($terceros, 'id_tercero_api'));
            $tercero = $key !== false ? ltrim($terceros[$key]['nom_tercero']) : '---';
            $ccnit = $key !== false ? number_format($terceros[$key]['nit_tercero'], 0, "", ".") : '---';

            $fecha = date('Y-m-d', strtotime($rp['fecha']));
            echo "<tr>
                <td style='text-align:left'>" . $rp['id_manu'] . "</td>
                <td style='text-align:left;white-space: nowrap;'>" .   $fecha   . "</td>
                <td style='text-align:left'>" .   $tercero . "</td>
                <td style='text-align:left;white-space: nowrap;'>" . $ccnit . "</td>
                <td style='text-align:left'>" . $rp['detalle'] . "</td>
                <td style='text-align:left'>" . $rp['rubro'] . "</td>
                <td style='text-align:left'>" .  $rp['nom_rubro'] . "</td>
                <td style='text-align:right'>" . number_format($rp['valor_pag'], 2, ".", ",")  . "</td>
                </tr>";
        }
        ?>
    </tbody>
</table>