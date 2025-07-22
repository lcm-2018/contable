<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

header("Content-type: application/vnd.ms-excel charset=utf-8");
header("Content-Disposition: attachment; filename=Cuentas_Pagar.xls");
header("Pragma: no-cache");
header("Expires: 0");

include '../../conexion.php';
$periodo = $_POST['periodo'];
$vigencia = $_SESSION['vigencia'];
$id_vigencia = $_SESSION['id_vigencia'];
$meses = '';
if ($periodo == 1) {
    $rango = "'$vigencia-01-01' AND '$vigencia-06-30'";
    $meses = 'JUNIO';
} else if ($periodo == 2) {
    $rango = "'$vigencia-07-01' AND '$vigencia-12-31'";
    $meses = 'DICIEMBRE';
} else {
    $rango = "'$vigencia-01-01' AND '$vigencia-12-31'";
    $meses = 'ANUAL';
}

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT
                DATE_FORMAT(`ctb_doc`.`fecha`, '%Y-%m-%d') AS `fecha`
                , CONCAT(`pto_sia`.`codigo` , `pto_cargue`.`cod_pptal`) AS `cod_ppto`
                , `pto_cargue`.`nom_rubro`
                , `pto_sia`.`codigo`
                , `banco`.`codigo` AS `fte`
                , `ctb_doc`.`id_manu`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `ctb_doc`.`detalle`
                , `tt`.`valor` 
                , IFNULL(`seguridad`.`valor`,0) AS `val_seg`
                , IFNULL(`retencion`.`valor`,0) AS `val_ret`
                , IFNULL(`neto`.`valor`,0) AS `val_neto`
                , `banco`.`cod_sia` AS `sia`
                , `banco`.`numero`
                , `tpdoc`.`documento`
            FROM
                `pto_pag_detalle`
                INNER JOIN `ctb_doc`
                    ON (`ctb_doc`.`id_ctb_doc` = `pto_pag_detalle`.`id_ctb_doc`)
                INNER JOIN `pto_cop_detalle` 
                    ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                INNER JOIN `pto_crp_detalle` 
                    ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                INNER JOIN `pto_cdp_detalle` 
                    ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                INNER JOIN `pto_cargue` 
                    ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                INNER JOIN `pto_homologa_gastos` 
                    ON (`pto_homologa_gastos`.`id_cargue` = `pto_cargue`.`id_cargue`)
                INNER JOIN `pto_sia` 
                    ON (`pto_homologa_gastos`.`id_sia` = `pto_sia`.`id_sia`)
                LEFT JOIN
                    (SELECT
                        `id_ctb_doc`, SUM(`valor`) AS `valor`, `id_tercero_api`
                    FROM
                        `pto_pag_detalle`
                    GROUP BY `id_ctb_doc`, `id_tercero_api`) AS `tt`
                    ON (`tt`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                LEFT JOIN `tb_terceros`
                    ON (`tt`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`)
                LEFT JOIN 
                    (SELECT 
                        `doc_pag` AS `id_ctb_doc`
                        , `seg`.`id_tercero_api`
                        , SUM(`seg`.`valor`) AS `valor`
                    FROM
                        (SELECT
                            `pto_cop_detalle`.`id_ctb_doc` AS `doc_cop`
                            , `pto_pag_detalle`.`id_ctb_doc` AS `doc_pag`
                        FROM
                            `pto_pag_detalle`
                            INNER JOIN `pto_cop_detalle` 
                            ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                        GROUP BY `pto_pag_detalle`.`id_ctb_doc`, `pto_cop_detalle`.`id_ctb_doc`) AS `id_docs`
                        INNER JOIN
                            (SELECT
                                `ctb_libaux`.`id_ctb_doc`
                                , `ctb_libaux`.`id_tercero_api`
                                , SUM(`ctb_libaux`.`credito`) AS `valor`
                            FROM
                                `ctb_libaux`
                                INNER JOIN `ctb_doc` 
                                ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                                INNER JOIN `ctb_pgcp` 
                                ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                            WHERE (`ctb_pgcp`.`cuenta` LIKE '2424%' AND `ctb_libaux`.`credito` > 0 AND `ctb_doc`.`estado` = 2)
                            GROUP BY `ctb_libaux`.`id_ctb_doc`, `ctb_libaux`.`id_tercero_api`)  AS `seg`
                            ON (`id_docs`.`doc_cop` = `seg`.`id_ctb_doc`)
                    GROUP BY `doc_pag`,`seg`.`id_tercero_api`) AS `seguridad`
                    ON (`seguridad`.`id_tercero_api` = `tt`.`id_tercero_api` AND `seguridad`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                LEFT JOIN 
                    (SELECT 
                        `doc_pag` AS `id_ctb_doc`
                        , `seg`.`id_tercero_api`
                        , SUM(`seg`.`valor`) AS `valor`
                    FROM
                        (SELECT
                            `pto_cop_detalle`.`id_ctb_doc` AS `doc_cop`
                            , `pto_pag_detalle`.`id_ctb_doc` AS `doc_pag`
                        FROM
                            `pto_pag_detalle`
                            INNER JOIN `pto_cop_detalle` 
                            ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                        GROUP BY `pto_pag_detalle`.`id_ctb_doc`, `pto_cop_detalle`.`id_ctb_doc`) AS `id_docs`
                        INNER JOIN
                            (SELECT
                                `ctb_libaux`.`id_ctb_doc`
                                , `ctb_libaux`.`id_tercero_api`
                                , SUM(`ctb_libaux`.`credito`) AS `valor`
                            FROM
                                `ctb_libaux`
                                INNER JOIN `ctb_doc` 
                                ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                                INNER JOIN `ctb_pgcp` 
                                ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                            WHERE (`ctb_pgcp`.`cuenta` LIKE '2436%' AND `ctb_libaux`.`credito` > 0 AND `ctb_doc`.`estado` = 2)
                            GROUP BY `ctb_libaux`.`id_ctb_doc`, `ctb_libaux`.`id_tercero_api`)  AS `seg`
                            ON (`id_docs`.`doc_cop` = `seg`.`id_ctb_doc`)
                    GROUP BY `doc_pag`,`seg`.`id_tercero_api`) AS `retencion`
                    ON (`retencion`.`id_tercero_api` = `tt`.`id_tercero_api` AND `retencion`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                LEFT JOIN
                    (SELECT 
                        `doc_pag` AS `id_ctb_doc`
                        , `seg`.`id_tercero_api`
                        , SUM(`seg`.`valor`) AS `valor`
                    FROM
                        (SELECT
                            `pto_cop_detalle`.`id_ctb_doc` AS `doc_cop`
                            , `pto_pag_detalle`.`id_ctb_doc` AS `doc_pag`
                        FROM
                            `pto_pag_detalle`
                            INNER JOIN `pto_cop_detalle` 
                            ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                        GROUP BY `pto_pag_detalle`.`id_ctb_doc`, `pto_cop_detalle`.`id_ctb_doc`) AS `id_docs`
                        INNER JOIN
                            (SELECT
                                `ctb_libaux`.`id_ctb_doc`
                                , `ctb_libaux`.`id_tercero_api`
                                , SUM(`ctb_libaux`.`credito`) AS `valor`
                            FROM
                                `ctb_libaux`
                                INNER JOIN `ctb_doc` 
                                ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                                INNER JOIN `ctb_pgcp` 
                                ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                            WHERE (`ctb_pgcp`.`cuenta` NOT LIKE '2424%' AND `ctb_pgcp`.`cuenta` NOT LIKE '2436%' AND `ctb_libaux`.`credito` > 0 AND `ctb_doc`.`estado` = 2)
                            GROUP BY `ctb_libaux`.`id_ctb_doc`, `ctb_libaux`.`id_tercero_api`)  AS `seg`
                            ON (`id_docs`.`doc_cop` = `seg`.`id_ctb_doc`)
                    GROUP BY `doc_pag`,`seg`.`id_tercero_api`) AS `neto` 
                    ON (`neto`.`id_tercero_api` = `tt`.`id_tercero_api` AND `neto`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                LEFT JOIN 
                    (SELECT
                        `ctb_libaux`.`id_ctb_doc`
                        , `fin_cod_fuente`.`codigo`
                        , `tb_bancos`.`cod_sia`
                        , `tes_cuentas`.`numero`
                    FROM
                        `ctb_libaux`
                        INNER JOIN `ctb_pgcp` 
                            ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                        INNER JOIN `tes_cuentas` 
                            ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                        INNER JOIN `fin_cod_fuente` 
                            ON (`tes_cuentas`.`id_fte` = `fin_cod_fuente`.`id`)
                        INNER JOIN `tb_bancos` 
                            ON (`tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)
                    WHERE (`ctb_libaux`.`credito` > 0 AND `ctb_pgcp`.`cuenta` LIKE '1110%')
                    GROUP BY `ctb_libaux`.`id_ctb_doc`) AS `banco`
                    ON (`banco`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                LEFT JOIN
                    (SELECT
                        `id_ctb_doc`, `documento`
                    FROM `tes_detalle_pago`
                    GROUP BY `id_ctb_doc`,`documento`) AS `tpdoc`
                    ON (`tpdoc`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
            WHERE DATE_FORMAT(`ctb_doc`.`fecha`, '%Y-%m-%d') BETWEEN $rango AND `ctb_doc`.`estado` = 2 AND `ctb_doc`.`id_tipo_doc` = 4 AND `pto_cargue`.`tipo_pto` = 8 
            GROUP BY `ctb_doc`.`id_ctb_doc`, `tt`.`id_tercero_api`
            ORDER BY `ctb_doc`.`fecha`, `ctb_doc`.`id_manu` ASC";
    $res = $cmd->query($sql);
    $lista = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$body = '';
foreach ($lista as $r) {
    $body .= "<tr>
                <td>{$r['cod_ppto']}</td>
                <td>{$r['nom_rubro']}</td>
                <td>{$r['valor']}</td>
                <td>{$r['fecha']}</td>
                <td>{$meses}</td>
                <td>{$r['id_manu']}</td>
                <td>{$r['nom_tercero']}</td>
                <td>{$r['nit_tercero']}</td>
                <td>{$r['detalle']}</td>
                <td>{$r['valor']}</td>
                <td>{$r['val_seg']}</td>
                <td>{$r['val_ret']}</td>
                <td>0</td>
                <td>{$r['val_neto']}</td>
                <td>{$r['sia']}</td>
                <td>{$r['numero']}</td>
                <td>{$r['documento']}</td>
            </tr>";
}
echo "\xEF\xBB\xBF";
?>
<table class="table-bordered bg-light" style="width:100% !important;" border=1>
    <tr>
        <td colspan="17" style="text-align: center; font-weight: bold;">CUENTAS POR PAGAR</td>
    </tr>
    <tr>
        <td colspan="17" style="text-align: center; font-weight: bold;">AÑO: <?= $vigencia; ?></td>
    </tr>
    <tr>
        <td colspan="17" style="text-align: center; font-weight: bold;">PERIODO: <?= $meses ?></td>
    </tr>
    <tr>
        <th>Código Presupuestal</th>
        <th>Descripción Del Rubro</th>
        <th>Cuenta Por Pagar Constituida</th>
        <th>Fecha De Pago</th>
        <th>Ultimo Mes Periodo Reportado</th>
        <th>No. De Comprobante</th>
        <th>Beneficiario</th>
        <th>Cédula O Nit</th>
        <th>Detalle De Pago</th>
        <th>Valor Comprobante De Pago</th>
        <th>Descuentos Seg. Social</th>
        <th>Descuentos Retenciones</th>
        <th>Otros Descuentos</th>
        <th>Neto Pagado</th>
        <th>Banco</th>
        <th>No. De Cuenta</th>
        <th>No. De Cheque O Nd</th>
    </tr>
    <tbody>
        <?= $body; ?>
    </tbody>
</table>