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
                `taux`.`no_crp`
                , `taux`.`no_cop`
                , `taux`.`fec_cop`
                , `taux`.`id_tercero_api`
                , `taux`.`detalle`
                , `taux`.`id_rubro`
                , `taux`.`rubro`
                , `taux`.`nom_rubro`
                , IFNULL(`t1`.`valor`,0) AS `valor_cop`
                , IFNULL(`t1`.`valor`,0) AS `valor_pag`
            FROM
                (SELECT
                    `pto_crp`.`id_manu` as `no_crp`
                    , `ctb_doc`.`id_ctb_doc`
                    ,`ctb_doc`.`id_manu` as `no_cop`
                    , `ctb_doc`.`fecha` AS `fec_cop`
                    , `pto_cop_detalle`.`id_tercero_api`
                    , `ctb_doc`.`detalle`
                    , `pto_cdp_detalle`.`id_rubro`
                    , `pto_cargue`.`cod_pptal` AS `rubro`
                    , `pto_cargue`.`nom_rubro`
                FROM
                    `pto_cop_detalle`
                    INNER JOIN `ctb_doc` 
                        ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    INNER JOIN `pto_crp_detalle` 
                        ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                    INNER JOIN `pto_crp` 
                        ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                    INNER JOIN `pto_cdp_detalle` 
                        ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                    INNER JOIN `pto_cargue` 
                        ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                WHERE (`ctb_doc`.`fecha` BETWEEN '2024-01-01' AND '2024-06-19' AND `ctb_doc`.`estado` <> 0)) AS `taux`
                LEFT JOIN 
                    (SELECT
                        `ctb_doc`.`id_ctb_doc`
                        , `pto_cdp_detalle`.`id_rubro`
                        , SUM(IFNULL(`pto_cop_detalle`.`valor`,0)) - SUM(IFNULL(`pto_cop_detalle`.`valor_liberado`,0)) AS `valor`
                    FROM
                        `pto_cop_detalle`
                        INNER JOIN `pto_crp_detalle` 
                            ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                        INNER JOIN `pto_cdp_detalle` 
                            ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                        INNER JOIN `ctb_doc` 
                            ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    WHERE (`ctb_doc`.`fecha` BETWEEN '2024-01-01' AND '2024-06-19' AND `ctb_doc`.`estado` <> 0)
                    GROUP BY `ctb_doc`.`id_ctb_doc`, `pto_cdp_detalle`.`id_rubro`) AS `t1`
                    ON (`taux`.`id_ctb_doc` = `t1`.`id_ctb_doc` AND `taux`.`id_rubro` = `t1`.`id_rubro`)
                LEFT JOIN
                    (SELECT
                        `pto_cdp_detalle`.`id_rubro`
                        , SUM(IFNULL(`pto_pag_detalle`.`valor`,0)) - SUM(IFNULL(`pto_pag_detalle`.`valor_liberado`,0)) AS `valor`
                        , `ctb_doc`.`id_ctb_doc`
                    FROM
                        `pto_cop_detalle`
                        INNER JOIN `pto_crp_detalle` 
                            ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                        INNER JOIN `pto_cdp_detalle` 
                            ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                        INNER JOIN `pto_pag_detalle` 
                            ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                        INNER JOIN `ctb_doc` AS `ctb_doc`
                            ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        INNER JOIN `ctb_doc` AS `ctb_doc_pag`
                            ON (`pto_pag_detalle`.`id_ctb_doc` = `ctb_doc_pag`.`id_ctb_doc`)
                    WHERE (`ctb_doc_pag`.`fecha` BETWEEN '2024-01-01' AND '2024-06-19' AND `ctb_doc_pag`.`estado` <> 0)
                    GROUP BY `pto_cdp_detalle`.`id_rubro`, `ctb_doc`.`id_ctb_doc`) AS `t2`
                    ON (`taux`.`id_ctb_doc` = `t2`.`id_ctb_doc` AND `taux`.`id_rubro` = `t2`.`id_rubro`)
            ORDER BY `taux`.`fec_cop` ASC";
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
$nom_informe = "RELACION DE OBLIGACIONES PRESUPUESTALES";
include_once '../../financiero/encabezado_empresa.php';
?>
<table class="table-hover" style="width:100% !important; border-collapse: collapse;" border="1">
    <thead>
        <tr class="centrar">
            <th>No causación</th>
            <th>No RP</th>
            <th>Fecha</th>
            <th>Tercero</th>
            <th>Cc/Nit</th>
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
            $key = array_search($rp['id_tercero_api'], array_column($terceros, 'id_tercero_api'));
            $tercero = $key !== false ? ltrim($terceros[$key]['nom_tercero']) : '---';
            $ccnit = $key !== false ? number_format($terceros[$key]['nit_tercero'], 0, "", ".") : '---';

            $fecha = date('Y-m-d', strtotime($rp['fec_cop']));
            $saldo = $rp['valor_cop'] - $rp['valor_pag'];
            echo "<tr>
                    <td style='text-align:left'>" . $rp['no_cop'] . "</td>
                    <td style='text-align:left'>" . $rp['no_crp'] . "</td>
                    <td style='text-align:left;white-space: nowrap;'>" . $fecha . "</td>
                    <td style='text-align:left'>" . $tercero . "</td>
                    <td style='text-align:right'>" . $ccnit . "</td>
                    <td style='text-align:left'>" . $rp['detalle'] . "</td>
                    <td style='text-align:left'>" . $rp['rubro'] . "</td>
                    <td style='text-align:left'>" . $rp['nom_rubro'] . "</td>
                    <td style='text-align:right'>" . number_format($rp['valor_cop'], 2, ".", ",") . "</td>
                    <td style='text-align:right'>" . number_format($saldo, 2, ".", ",") . "</td>
                </tr>";
        }
        ?>
    </tbody>
</table>