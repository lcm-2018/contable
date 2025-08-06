<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

header("Content-type: application/vnd.ms-excel charset=utf-8");
header("Content-Disposition: attachment; filename=Traslado_Fondos.xls");
header("Pragma: no-cache");
header("Expires: 0");

include '../../conexion.php';
$periodo = $_POST['periodo'];
$vigencia = $_SESSION['vigencia'];
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
                , `ctb_doc`.`id_manu`
                , DATE_FORMAT(`ctb_doc`.`fecha`, '%Y-%m-%d') AS `fecha`
                , `ctb_pgcp`.`cuenta` AS `deb_cuenta`
                , `tb_bancos`.`cod_sia` AS `deb_sia`
                , `tes_cuentas`.`numero` AS `deb_numero`
                , `fin_cod_fuente`.`codigo` AS `deb_codigo`
                , `ctb_libaux`.`debito`
                , `cred`.`cuenta` AS `cre_cuenta`
                , `cred`.`cod_sia` AS `cre_sia`
                , `cred`.`numero` AS `cre_numero`
                , `cred`.`codigo` AS `cre_codigo`
                , `cred`.`credito`
            FROM
                `ctb_libaux`
                INNER JOIN `ctb_doc` 
                    ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `ctb_pgcp` 
                    ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                LEFT JOIN `tes_cuentas` 
                    ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                LEFT JOIN `fin_cod_fuente` 
                    ON (`tes_cuentas`.`id_fte` = `fin_cod_fuente`.`id`)
                LEFT JOIN `tb_bancos` 
                    ON (`tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)
                LEFT JOIN 
                (SELECT
                    `ctb_doc`.`id_ctb_doc`
                    , `ctb_pgcp`.`cuenta`
                    , `tb_bancos`.`cod_sia`
                    , `tes_cuentas`.`numero`
                    , `fin_cod_fuente`.`codigo`
                    , `ctb_libaux`.`credito`
                FROM
                    `ctb_libaux`
                    INNER JOIN `ctb_doc` 
                    ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    INNER JOIN `ctb_pgcp` 
                    ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                    LEFT JOIN `tes_cuentas` 
                    ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                    LEFT JOIN `fin_cod_fuente` 
                    ON (`tes_cuentas`.`id_fte` = `fin_cod_fuente`.`id`)
                    LEFT JOIN `tb_bancos` 
                    ON (`tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)
                WHERE (`ctb_doc`.`id_tipo_doc` = 10 AND `ctb_libaux`.`credito` > 0 AND `ctb_doc`.`estado` = 2)) `cred`
                ON (`ctb_doc`.`id_ctb_doc` = `cred`.`id_ctb_doc`)
            WHERE (`ctb_doc`.`id_tipo_doc` = 10 AND `ctb_libaux`.`debito` > 0 AND `ctb_doc`.`estado` = 2 AND `tes_cuentas`.`estado` = 1
                AND DATE_FORMAT(`ctb_doc`.`fecha`, '%Y-%m-%d') BETWEEN $rango)
            ORDER BY DATE_FORMAT(`ctb_doc`.`fecha`, '%Y-%m-%d'),`tb_bancos`.`cod_sia` ASC";
    $res = $cmd->query($sql);
    $lista = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$body = '';
foreach ($lista as $r) {
    $body .= "<tr>
                <td>{$r['id_manu']}</td>
                <td>{$r['fecha']}</td>
                <td>{$r['cre_cuenta']}</td>
                <td>{$r['cre_sia']}</td>
                <td>{$r['cre_numero']}</td>
                <td>{$r['cre_codigo']}</td>
                <td>{$r['debito']}</td>
                <td>{$r['deb_sia']}</td>
                <td>{$r['deb_numero']}</td>
                <td>{$r['deb_codigo']}</td>
                <td>{$meses}</td>
                <td>{$r['deb_cuenta']}</td>
            </tr>";
}
echo "\xEF\xBB\xBF";
?>
<table class="table-bordered bg-light" style="width:100% !important;" border=1>
    <tr>
        <td colspan="12" style="text-align: center; font-weight: bold;">TRASLADO DE FONDOS</td>
    </tr>
    <tr>
        <td colspan="12" style="text-align: center; font-weight: bold;">AÑO: <?= $vigencia; ?></td>
    </tr>
    <tr>
        <td colspan="12" style="text-align: center; font-weight: bold;">PERIODO: <?= $meses ?></td>
    </tr>
    <tr>
        <th>Documento</th>
        <th>Fecha</th>
        <th>Cuenta Contable</th>
        <th>Banco Origen</th>
        <th>Numero Cuenta Bancaria</th>
        <th>Fuente De Financiación</th>
        <th>Valor Traslado</th>
        <th>Banco Receptor</th>
        <th>Numero Cuenta Bancaria</th>
        <th>Fuente De Financiación</th>
        <th>Periodo Reportado</th>
        <th>Cuenta Contable</th>
    </tr>
    <tbody>
        <?= $body; ?>
    </tbody>
</table>