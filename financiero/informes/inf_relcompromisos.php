<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

header("Content-type: application/vnd.ms-excel charset=utf-8");
header("Content-Disposition: attachment; filename=Relacion_Compromisos.xls");
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
                `taux`.`id_rubro`
                , CONCAT(IFNULL(`pto_sia`.`codigo`,''), `pto_cargue`.`cod_pptal`) AS `cod_rubro`
                , `pto_cargue`.`nom_rubro`
                , `pto_cdp`.`id_manu`
                , DATE_FORMAT(`pto_cdp`.`fecha`,'%Y-%m-%d') AS `fecha`
                , `tt`.`valor` AS `val_cdp`
                , DATE_FORMAT(`pto_crp`.`fecha`,'%Y-%m-%d') AS `fecha`
                , `taux`.`valor`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `pto_crp`.`objeto`
            FROM
                (SELECT
                    `crp`.`id_cdp`
                    , `crp`.`id_pto_crp`
                    , `cdp_det`.`id_rubro`
                    , SUM(IF(DATE_FORMAT(`crp`.`fecha`,'%Y-%m-%d') BETWEEN $rango, IFNULL(`det`.`valor`, 0), 0)) - SUM(IF(DATE_FORMAT(`det`.`fecha_libera`,'%Y-%m-%d') BETWEEN $rango, IFNULL(`det`.`valor_liberado`, 0), 0)) AS `valor`
                    , `crp`.`id_tercero_api`
                FROM
                    `pto_crp_detalle` `det`
                INNER JOIN `pto_crp` `crp` 
                    ON (`det`.`id_pto_crp` = `crp`.`id_pto_crp`)
                INNER JOIN `pto_cdp_detalle` `cdp_det` 
                    ON (`det`.`id_pto_cdp_det` = `cdp_det`.`id_pto_cdp_det`)
                WHERE `crp`.`estado` = 2
                    AND (DATE_FORMAT(`crp`.`fecha`,'%Y-%m-%d') BETWEEN $rango OR DATE_FORMAT(`det`.`fecha_libera`,'%Y-%m-%d') BETWEEN $rango)
                GROUP BY  `crp`.`id_pto_crp`, `crp`.`id_cdp`, `cdp_det`.`id_rubro`, `crp`.`id_tercero_api`) AS `taux`
                INNER JOIN `pto_cdp`
                    ON (`taux`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
                INNER JOIN `pto_crp`
                    ON (`taux`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                INNER JOIN `pto_cargue` 
                    ON (`pto_cargue`.`id_cargue` = `taux`.`id_rubro`)
                INNER JOIN `pto_homologa_ingresos` 
                    ON (`pto_homologa_ingresos`.`id_cargue` = `pto_cargue`.`id_cargue`)
                INNER JOIN `pto_sia` 
                    ON (`pto_homologa_ingresos`.`id_sia` = `pto_sia`.`id_sia`)
                LEFT JOIN
                    (SELECT 
                        `cdp`.`id_pto_cdp`,
                        IFNULL(SUM(`det`.`valor`), 0) - IFNULL(SUM(`det`.`valor_liberado`), 0) AS `valor`
                    FROM 
                        `pto_cdp` `cdp`
                        INNER JOIN `pto_cdp_detalle` `det` 
                            ON (`det`.`id_pto_cdp` = `cdp`.`id_pto_cdp`)
                    WHERE `cdp`.`estado` = 2 
                        AND (DATE_FORMAT(`cdp`.`fecha`,'%Y-%m-%d') BETWEEN $rango 
                        OR DATE_FORMAT(`det`.`fecha_libera`,'%Y-%m-%d') BETWEEN $rango)
                    GROUP BY `cdp`.`id_pto_cdp`) AS `tt`
                    ON (`taux`.`id_cdp` = `tt`.`id_pto_cdp`)
                LEFT JOIN `tb_terceros`
                    ON (`tb_terceros`.`id_tercero_api` = `taux`.`id_tercero_api`)
            ORDER BY DATE_FORMAT(`pto_cdp`.`fecha`,'%Y-%m-%d'), `pto_cdp`.`id_manu` ASC";
    $res = $cmd->query($sql);
    $lista = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$body = '';
foreach ($lista as $r) {
    $body .=
        <<<HTML
            <tr>
                <td>{$r['cod_rubro']}</td>
                <td>{$r['nom_rubro']}</td>
                <td>{$r['id_manu']}</td>
                <td>{$r['fecha']}</td>
                <td>{$meses}</td>
                <td>{$r['val_cdp']}</td>
                <td>{$r['fecha']}</td>
                <td>{$r['valor']}</td>
                <td>{$r['nom_tercero']}</td>
                <td>{$r['nit_tercero']}</td>
                <td>{$r['objeto']}</td>
            </tr>
        HTML;
}
echo "\xEF\xBB\xBF";
?>
<table class="table-bordered bg-light" style="width:100% !important;" border=1>
    <tr>
        <td colspan="11" style="text-align: center; font-weight: bold;">RELACIÓN DE COMPROMISOS</td>
    </tr>
    <tr>
        <td colspan="11" style="text-align: center; font-weight: bold;">AÑO: <?= $vigencia; ?></td>
    </tr>
    <tr>
        <td colspan="11" style="text-align: center; font-weight: bold;">PERIODO: <?= $meses ?></td>
    </tr>
    <tr>
        <th>Código Rubro Presupuestal</th>
        <th>Nombre Rubro Presupuestal</th>
        <th>Numero del Cdp</th>
        <th>Fecha del Cdp</th>
        <th>Periodo reportado</th>
        <th>Valor del Cdp</th>
        <th>Fecha De Registro Presupuestal</th>
        <th>Valor del Registro Presupuestal</th>
        <th>Beneficiario</th>
        <th>Cedula o Nit</th>
        <th>Detalle Del Compromiso</th>
    </tr>
    <tbody>
        <?= $body; ?>
    </tbody>
</table>