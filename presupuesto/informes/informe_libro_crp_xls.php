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
                `taux`.`no_cdp`
                , `taux`.`fec_cdp`
                , `taux`.`no_rp`
                , `taux`.`fec_rp`
                , `taux`.`id_tercero_api`
                , `taux`.`objeto`
                , `taux`.`id_rubro`
                , `taux`.`rubro`
                , `taux`.`nom_rubro`
                , IFNULL(`t1`.`valor`,0) AS `val_crp` 
                , IFNULL(`t2`.`valor`,0) AS `val_cop`
                , `ctt_contratos`.`num_contrato`
            FROM 
                (SELECT
                    `pto_cdp`.`id_pto_cdp`
                    ,`pto_cdp`.`id_manu` AS `no_cdp`
                    , `pto_cdp`.`fecha` AS `fec_cdp`
                    , `pto_crp`.`id_manu` AS `no_rp`
                    , `pto_crp`.`fecha` AS `fec_rp`
                    , `pto_crp_detalle`.`id_tercero_api`
                    , `pto_crp`.`objeto`
                    , `pto_cdp_detalle`.`id_rubro`
                    , `pto_cargue`.`cod_pptal` AS `rubro`
                    , `pto_cargue`.`nom_rubro`
                FROM
                    `pto_cdp_detalle`
                    INNER JOIN `pto_cdp` 
                        ON (`pto_cdp_detalle`.`id_pto_cdp` = `pto_cdp`.`id_pto_cdp`)
                    INNER JOIN `pto_crp_detalle` 
                        ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                    INNER JOIN `pto_crp` 
                        ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                    INNER JOIN `pto_cargue` 
                        ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                WHERE (`pto_crp`.`fecha` BETWEEN '2024-01-01' AND '2024-06-19' AND `pto_crp`.`estado` <> 0)) AS `taux`
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
                        WHERE (`pto_crp`.`fecha` BETWEEN '2024-01-01' AND '2024-06-19' AND `pto_crp`.`estado` <> 0)
                        GROUP BY `pto_cdp_detalle`.`id_pto_cdp`, `pto_cdp_detalle`.`id_rubro`) AS `t1`
                    ON (`t1`.`id_pto_cdp` = `taux`.`id_pto_cdp` AND `t1`.`id_rubro` = `taux`.`id_rubro`)
                LEFT JOIN
                        (SELECT
                            `pto_cdp_detalle`.`id_pto_cdp`
                            , `pto_cdp_detalle`.`id_rubro`
                            , SUM(IFNULL(`pto_cop_detalle`.`valor`,0)) - SUM(IFNULL(`pto_cop_detalle`.`valor_liberado`,0)) AS `valor`
                        FROM
                            `pto_crp_detalle`
                            INNER JOIN `pto_cdp_detalle` 
                                ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                            INNER JOIN `pto_crp` 
                                ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                            INNER JOIN `pto_cop_detalle` 
                                ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                        WHERE (`pto_crp`.`fecha` BETWEEN '2024-01-01' AND '2024-06-19' AND `pto_crp`.`estado` <> 0)
                        GROUP BY `pto_cdp_detalle`.`id_pto_cdp`, `pto_cdp_detalle`.`id_rubro`) AS `t2`
                            ON (`t2`.`id_pto_cdp` = `taux`.`id_pto_cdp` AND `t2`.`id_rubro` = `taux`.`id_rubro`)
                LEFT JOIN `ctt_adquisiciones` 
                    ON (`ctt_adquisiciones`.`id_cdp` = `taux`.`id_pto_cdp`)
                LEFT JOIN `ctt_contratos` 
                    ON (`ctt_contratos`.`id_compra` = `ctt_adquisiciones`.`id_adquisicion`)
            ORDER BY `taux`.`fec_rp` ASC";
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
$nom_informe = "RELACION DE REGISTROS PRESUPUESTALES";
include_once '../../financiero/encabezado_empresa.php';
?>
<table class="table-hover" style="width:100% !important; border-collapse: collapse;" border="1">
    <thead>
        <tr style="text-align: center;">
            <th>CDP</th>
            <th>Fecha CDP</th>
            <th>RP</th>
            <th>Fecha RP</th>
            <th>Tercero</th>
            <th>Cc/Nit</th>
            <th>No Contrato</th>
            <th>Objeto</th>
            <th>Rubro</th>
            <th>Nombre Rubro</th>
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

            $fec_cdp = date('Y-m-d', strtotime($rp['fec_cdp']));
            $fec_rp = date('Y-m-d', strtotime($rp['fec_rp']));
            $valor = $rp['val_crp'];
            $saldo = $rp['val_crp'] - $rp['val_cop'];
            echo "<tr>
                <td style='text-align:left'>" . $rp['no_cdp'] . "</td>
                <td style='text-align:left;white-space: nowrap;'>" . $fec_cdp   . "</td>
                <td style='text-align:left'>" . $rp['no_rp'] . "</td>
                <td style='text-align:left;white-space: nowrap;'>" . $fec_rp   . "</td>
                <td style='text-align:left'>" . $tercero . "</td>
                <td style='text-align:right'>" . $ccnit . "</td>
                <td style='text-align:left'>" . $rp['num_contrato'] . "</td>
                <td style='text-align:left'>" . $rp['objeto'] . "</td>
                <td style='text-align:left'>" . $rp['rubro'] . "</td>
                <td style='text-align:left'>" . $rp['nom_rubro'] . "</td>
                <td style='text-align:right'>" . number_format($valor, 2, ".", ",")  . "</td>
                <td style='text-align:right'>" . number_format($saldo, 2, ".", ",")  . "</td>
                </tr>";
        }
        ?>
    </tbody>
</table>