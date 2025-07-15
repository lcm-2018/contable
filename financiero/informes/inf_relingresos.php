<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

header("Content-type: application/vnd.ms-excel charset=utf-8");
header("Content-Disposition: attachment; filename=Relacion_Ingresos.xls");
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
                `pto_cargue`.`id_cargue`
                , CONCAT(`pto_sia`.`codigo`, `pto_cargue`.`cod_pptal`) AS `cod_rubro`
                , `pto_cargue`.`nom_rubro`
                , `ingresos`.`fecha`
                , `ingresos`.`id_manu`
                , `ingresos`.`nom_tercero`
                , `ingresos`.`objeto`
                , `ingresos`.`valor`
                , `ingresos`.`liberado`
                , `ingresos`.`cuenta`
                , `ingresos`.`banco`
            FROM
                `pto_cargue`
                INNER JOIN `pto_presupuestos` 
                    ON (`pto_cargue`.`id_pto` = `pto_presupuestos`.`id_pto`)
                INNER JOIN `pto_homologa_ingresos` 
                    ON (`pto_homologa_ingresos`.`id_cargue` = `pto_cargue`.`id_cargue`)
                INNER JOIN `pto_sia` 
                    ON (`pto_homologa_ingresos`.`id_sia` = `pto_sia`.`id_sia`)
                LEFT JOIN
                (SELECT 
                    `recaudo`.`id_rubro`
                    , DATE_FORMAT(`pto_rec`.`fecha`,'%Y-%m-%d') AS `fecha`
                    , `pto_rec`.`id_manu`
                    , `tb_terceros`.`nom_tercero`
                    , `pto_rec`.`objeto`
                    , `recaudo`.`valor`
                    , `recaudo`.`liberado`
                    , '1111' AS `cuenta`
                    , 'b5' AS `banco`
                FROM `pto_rec`
                    INNER JOIN
                        (SELECT
                            `pto_rec`.`id_pto_rec`
                            , CASE
                            WHEN `pto_rec_detalle`.`id_rubro` IS NULL THEN `pto_rad_detalle`.`id_rubro` 
                            ELSE `pto_rec_detalle`.`id_rubro`
                            END AS `id_rubro` 
                            , SUM(IFNULL(`pto_rec_detalle`.`valor`,0)) AS `valor`
                            , SUM(IFNULL(`pto_rec_detalle`.`valor_liberado`,0)) AS `liberado`
                        FROM
                            `pto_rec_detalle`
                            INNER JOIN `pto_rec` 
                            ON (`pto_rec_detalle`.`id_pto_rac` = `pto_rec`.`id_pto_rec`)
                            LEFT JOIN `pto_rad_detalle` 
                            ON (`pto_rec_detalle`.`id_pto_rad_detalle` = `pto_rad_detalle`.`id_pto_rad_det`)
                        WHERE (DATE_FORMAT(`pto_rec`.`fecha`,'%Y-%m-%d') BETWEEN $rango AND `pto_rec`.`estado` = 2)
                        GROUP BY `id_rubro`, `pto_rec`.`id_pto_rec`) AS `recaudo`
                        ON (`recaudo`.`id_pto_rec` = `pto_rec`.`id_pto_rec`)
                    LEFT JOIN `tb_terceros`
                        ON (`tb_terceros`.`id_tercero_api` = `pto_rec`.`id_tercero_api`)) AS `ingresos`
                ON (`ingresos`.`id_rubro` = `pto_cargue`.`id_cargue`)
            WHERE (`pto_presupuestos`.`id_tipo` = 1 AND `pto_presupuestos`.`id_vigencia` = $id_vigencia AND `ingresos`.`id_rubro` IS NOT NULL)
            ORDER BY `pto_cargue`.`id_cargue` ASC";
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
                <td>{$r['fecha']}</td>
                <td>{$meses}</td>
                <td>{$r['id_manu']}</td>
                <td>{$r['nom_tercero']}</td>
                <td>{$r['objeto']}</td>
                <td>{$r['valor']}</td>
                <td>{$r['liberado']}</td>
                <td>{$r['cuenta']}</td>
                <td>{$r['banco']}</td>
            </tr>
        HTML;
}
echo "\xEF\xBB\xBF";
?>
<table class="table-bordered bg-light" style="width:100% !important;" border=1>
    <tr>
        <td colspan="10" style="text-align: center; font-weight: bold;">EJECUCIÓN PRESUPUESTAL DE INGRESOS</td>
    </tr>
    <tr>
        <td colspan="10" style="text-align: center; font-weight: bold;">AÑO: <?= $vigencia; ?></td>
    </tr>
    <tr>
        <td colspan="10" style="text-align: center; font-weight: bold;">PERIODO: <?= $meses ?></td>
    </tr>
    <tr>
        <th>Código Presupuestal</th>
        <th>Fecha De Recaudo</th>
        <th>Periodo reportado</th>
        <th>Numero De Recibo</th>
        <th>Recibido De</th>
        <th>Concepto Recaudo</th>
        <th>Valor</th>
        <th>Liberado</th>
        <th>No.Cuenta Bancaria Destino</th>
        <th>Banco</th>
    </tr>
    <tbody>
        <?= $body; ?>
    </tbody>
</table>