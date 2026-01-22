<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

header("Content-type: application/vnd.ms-excel charset=utf-8");
header("Content-Disposition: attachment; filename=Relacion_Gastos_Sin_Pto.xls");
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
                `ctb_doc`.`id_ctb_doc`
                , DATE_FORMAT(`ctb_doc`.`fecha`, '%Y-%m-%d') AS `fecha`
                , `ctb_doc`.`id_manu`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `ctb_doc`.`detalle`
                , SUM(`ctb_libaux`.`debito`) AS `valor`
                , 0.00 AS `descuento`
                , SUM(`ctb_libaux`.`debito`) AS `neto`
                , `banco`.`cod_sia` AS `sia`
                , `banco`.`numero`
                , `tpdoc`.`documento`
            FROM
                `ctb_doc`
                INNER JOIN `ctb_libaux` 
                    ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                LEFT JOIN `pto_pag_detalle` 
                    ON (`pto_pag_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                LEFT JOIN `tb_terceros`
                    ON (`tb_terceros`.`id_tercero_api` = `ctb_doc`.`id_tercero`)
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
            WHERE (`ctb_doc`.`id_tipo_doc` = 4 AND `pto_pag_detalle`.`id_ctb_doc` IS NULL AND `ctb_doc`.`estado` = 2
                AND DATE_FORMAT(`ctb_doc`.`fecha`, '%Y-%m-%d') BETWEEN $rango)
            GROUP BY `ctb_doc`.`id_ctb_doc`
            ORDER BY `ctb_doc`.`fecha`, `ctb_doc`.`id_manu` ASC";
    $res = $cmd->query($sql);
    $lista = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$body = '';
foreach ($lista as $r) {
    $body .= "<tr>
                <td>{$r['fecha']}</td>
                <td>{$meses}</td>
                <td>{$r['id_manu']}</td>
                <td>{$r['nom_tercero']}</td>
                <td>{$r['nit_tercero']}</td>
                <td>{$r['detalle']}</td>
                <td>{$r['valor']}</td>
                <td>{$r['descuento']}</td>
                <td>{$r['neto']}</td>
                <td>{$r['sia']}</td>
                <td>{$r['numero']}</td>
                <td>{$r['documento']}</td>
            </tr>";
}
echo "\xEF\xBB\xBF";
?>
<table class="table-bordered bg-light" style="width:100% !important;" border=1>
    <tr>
        <td colspan="12" style="text-align: center; font-weight: bold;">RELACIÓN DE PAGOS SIN AFECTACIÓN PRESUPUESTAL</td>
    </tr>
    <tr>
        <td colspan="12" style="text-align: center; font-weight: bold;">AÑO: <?= $vigencia; ?></td>
    </tr>
    <tr>
        <td colspan="12" style="text-align: center; font-weight: bold;">PERIODO: <?= $meses ?></td>
    </tr>
    <tr>
        <th>Fecha De Pago</th>
        <th>Periodo reporte</th>
        <th>No. De Comprobante</th>
        <th>Beneficiario</th>
        <th>Cédula O Nit</th>
        <th>Detalle De Pago</th>
        <th>Valor Comprobante De Pago</th>
        <th>Descuentos</th>
        <th>Neto Pagado</th>
        <th>Banco</th>
        <th>No. De Cuenta</th>
        <th>No. De Cheque O Nd</th>
    </tr>
    <tbody>
        <?= $body; ?>
    </tbody>
</table>