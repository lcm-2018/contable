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
                `pto_crp`.`id_pto_crp`
                , `pto_cdp_detalle`.`id_rubro`
                , `pto_crp`.`id_manu`
                , `pto_crp`.`fecha`
                , `pto_cargue`.`cod_pptal` AS `rubro`
                , `pto_cargue`.`nom_rubro`
                , `pto_crp`.`objeto`
                , `ctt_contratos`.`num_contrato`
                , IFNULL(`t1`.`val_crp`,0) AS `val_crp`
                , IFNULL(`t2`.`val_cop`,0) AS `val_cop`
                , IFNULL(`t3`.`val_pag`,0) AS `val_pag`
                , `pto_crp_detalle`.`id_tercero_api`
            FROM
                `pto_crp_detalle`
                INNER JOIN `pto_cdp_detalle` 
                    ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                INNER JOIN `pto_cargue` 
                    ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                INNER JOIN `pto_crp` 
                    ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                INNER JOIN `pto_cdp` 
                    ON (`pto_crp`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
                LEFT JOIN `ctt_adquisiciones` 
                    ON (`ctt_adquisiciones`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
                LEFT JOIN `ctt_contratos` 
                    ON (`ctt_contratos`.`id_compra` = `ctt_adquisiciones`.`id_adquisicion`)
                LEFT JOIN
                    (SELECT
                        `pto_crp`.`id_pto_crp`
                        , `pto_cdp_detalle`.`id_rubro`
                        , SUM(IFNULL(`pto_crp_detalle`.`valor`,0)) - SUM(IFNULL(`pto_crp_detalle`.`valor_liberado`,0)) AS `val_crp`
                    FROM
                        `pto_crp_detalle`
                        INNER JOIN `pto_cdp_detalle` 
                        ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                        INNER JOIN `pto_crp` 
                        ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                    WHERE (`pto_crp`.`fecha` BETWEEN '2024-01-01' AND '2024-06-11' AND `pto_crp`.`estado` <> 0)
                    GROUP BY `pto_crp`.`id_pto_crp`, `pto_cdp_detalle`.`id_rubro`) AS `t1`
                    ON(`t1`.`id_pto_crp` =  `pto_crp`.`id_pto_crp` AND `t1`.`id_rubro` = `pto_cdp_detalle`.`id_rubro`)
                LEFT JOIN
                    (SELECT
                        `pto_crp`.`id_pto_crp`
                        , `pto_cdp_detalle`.`id_rubro`
                        , SUM(IFNULL(`pto_cop_detalle`.`valor`,0)) - SUM(IFNULL(`pto_cop_detalle`.`valor_liberado`,0)) AS `val_cop`
                    FROM
                        `pto_crp_detalle`
                        INNER JOIN `pto_cdp_detalle` 
                        ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                        INNER JOIN `pto_crp` 
                        ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                        INNER JOIN `pto_cop_detalle` 
                        ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                        INNER JOIN `ctb_doc` 
                        ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    WHERE (`ctb_doc`.`fecha` BETWEEN '2024-01-01' AND '2024-06-11' AND `ctb_doc`.`estado` <> 0)
                    GROUP BY `pto_crp`.`id_pto_crp`, `pto_cdp_detalle`.`id_rubro`) AS `t2`
                    ON(`t2`.`id_pto_crp` =  `pto_crp`.`id_pto_crp` AND `t2`.`id_rubro` = `pto_cdp_detalle`.`id_rubro`)
                LEFT JOIN
                    (SELECT
                        `pto_crp`.`id_pto_crp`
                        , `pto_cdp_detalle`.`id_rubro`
                        , SUM(IFNULL(`pto_pag_detalle`.`valor`,0)) - SUM(IFNULL(`pto_pag_detalle`.`valor_liberado`,0)) AS `val_pag`
                    FROM
                        `pto_crp_detalle`
                        INNER JOIN `pto_cdp_detalle` 
                        ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                        INNER JOIN `pto_crp` 
                        ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                        INNER JOIN `pto_cop_detalle` 
                        ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                        INNER JOIN `pto_pag_detalle` 
                        ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                        INNER JOIN `ctb_doc` 
                        ON (`pto_pag_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    WHERE (`ctb_doc`.`fecha` BETWEEN '2024-01-01' AND '2024-06-11'AND `ctb_doc`.`estado` <> 0)
                    GROUP BY `pto_crp`.`id_pto_crp`, `pto_cdp_detalle`.`id_rubro`) AS `t3`
                    ON(`t3`.`id_pto_crp` =  `pto_crp`.`id_pto_crp` AND `t3`.`id_rubro` = `pto_cdp_detalle`.`id_rubro`)
            WHERE (`pto_crp`.`fecha` BETWEEN '2024-01-01' AND '2024-06-11' AND `pto_crp`.`estado` <> 0)
            ORDER BY `pto_crp`.`fecha` ASC";
    $res = $cmd->query($sql);
    $consolidado = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$terceros = [];
if (!empty($consolidado)) {
    $id_t = [];
    foreach ($consolidado as $ca) {
        if ($ca['id_tercero_api'] != '') {
            $id_t[] = $ca['id_tercero_api'];
        }
    }
    $ids = implode(',', $id_t);
    $terceros = getTerceros($ids, $cmd);
}
$nom_informe = "RELACION DE COMPROMISOS Y CUENTAS POR PAGAR";
include_once '../../financiero/encabezado_empresa.php';
?>
<table class="table-hover" style="width:100% !important; border-collapse: collapse;" border="1">
    <thead>
        <tr class="centrar">
            <th>Fecha</th>
            <th>No CRP</th>
            <th>No Contrato</th>
            <th>Tercero</th>
            <th>CC/NIT</th>
            <th>Detalle</th>
            <th>Rubro</th>
            <th>Valor registrado</th>
            <th>Valor causado</th>
            <th>Valor Pagado</th>
            <th>Compromisos por pagar</th>
            <th>Cuentas por pagar</th>
            <th>Auxiliar</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($consolidado as $rp) {
            $key = array_search($rp['id_tercero_api'], array_column($terceros, 'id_tercero_api'));
            $tercero = $key !== false ? ltrim($terceros[$key]['nom_tercero']) : '---';
            $ccnit = $key !== false ? number_format($terceros[$key]['nit_tercero'], 0, "", ".") : '---';

            $fecha = date('Y-m-d', strtotime($rp['fecha']));
            $valor = $rp['val_crp'];
            if ($valor > 0) {
                echo "<tr>
                <td style='text-align:left;white-space: nowrap;'>" . $fecha .  "</td>
                <td style='text-align:left'>" . $rp['id_manu'] . "</td>
                <td style='text-align:left'>" . $rp['num_contrato'] . "</td>
                <td style='text-align:left'>" . $tercero  . "</td>
                <td style='text-align:right'>" . $ccnit  . "</td>
                <td style='text-align:left'>" . $rp['objeto'] . "</td>
                <td style='text-align:left'>" . $rp['rubro']   . "</td>
                <td style='text-align:right'>" . number_format($valor, 2, ".", ",")   . "</td>
                <td style='text-align:right'>" .  number_format($rp['val_cop'], 2, ".", ",")  . "</td>
                <td style='text-align:right'>" .  number_format($rp['val_pag'], 2, ".", ",")  . "</td>
                <td style='text-align:right'>" .  number_format(($valor - $rp['val_cop']), 2, ".", ",")  . "</td>
                <td style='text-align:right'>" .  number_format(($rp['val_cop'] - $rp['val_pag']), 2, ".", ",")  . "</td>
                <td style='text-align:right'></td>
                </tr>";
            }
        }
        ?>
    </tbody>
</table>