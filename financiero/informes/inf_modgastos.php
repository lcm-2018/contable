<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

header("Content-type: application/vnd.ms-excel charset=utf-8");
header("Content-Disposition: attachment; filename=Modificacion_Gastos.xls");
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
                `pto_mod`.`id_pto_mod`
                , `pto_cargue`.`id_cargue`
                , `pto_actos_admin`.`nombre`
                , CONCAT(IFNULL(`pto_sia`.`codigo`,''),`pto_cargue`.`cod_pptal`) AS `cod_pptal`
                , `pto_mod`.`numero_acto`
                , DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') AS `fecha`
                , IFNULL(`adicion`.`valor`,0) AS `val_adicion`
                , IFNULL(`reduccion`.`valor`,0) AS `val_reduccion`
                , IFNULL(`credito`.`valor`,0) AS `val_credito`
                , IFNULL(`contracredito`.`valor`,0) AS `val_contracredito`
                , 0 AS `val_aplazamiento`
                , 0 AS `val_desaplazamiento`
            FROM `pto_cargue`
                JOIN `pto_mod`
                INNER JOIN `pto_presupuestos` 
                    ON (`pto_cargue`.`id_pto` = `pto_presupuestos`.`id_pto`)
                LEFT JOIN `pto_homologa_gastos` 
                    ON (`pto_homologa_gastos`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN `pto_sia` 
                    ON (`pto_homologa_gastos`.`id_sia` = `pto_sia`.`id_sia`)
                INNER JOIN `pto_actos_admin` 
                    ON (`pto_mod`.`id_tipo_acto` = `pto_actos_admin`.`id_acto`)
                LEFT JOIN
                    (SELECT
                        `pto_mod`.`id_pto_mod`
                        , `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_deb`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod`
                        ON (`pto_mod`.`id_pto_mod` = `pto_mod_detalle`.`id_pto_mod`)
                    WHERE `pto_mod`.`id_tipo_mod` = 2 AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN $rango
                    GROUP BY `id_pto_mod`, `id_cargue`) AS `adicion`
                    ON (`adicion`.`id_pto_mod` = `pto_mod`.`id_pto_mod` AND `pto_cargue`.`id_cargue` = `adicion`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_mod`.`id_pto_mod`
                        , `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_cred`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod`
                        ON (`pto_mod`.`id_pto_mod` = `pto_mod_detalle`.`id_pto_mod`)
                    WHERE `pto_mod`.`id_tipo_mod` = 3 AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN $rango
                    GROUP BY `id_pto_mod`, `id_cargue`) AS `reduccion`
                    ON (`pto_mod`.`id_pto_mod` = `reduccion`.`id_pto_mod` AND `pto_cargue`.`id_cargue` = `reduccion`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_mod`.`id_pto_mod`
                        , `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_deb`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod`
                        ON (`pto_mod`.`id_pto_mod` = `pto_mod_detalle`.`id_pto_mod`)
                    WHERE `pto_mod`.`id_tipo_mod` IN (1,6) AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN $rango
                    GROUP BY `id_pto_mod`, `id_cargue`) AS `credito`
                    ON (`pto_mod`.`id_pto_mod` = `credito`.`id_pto_mod` AND `pto_cargue`.`id_cargue` = `credito`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_mod`.`id_pto_mod`
                        , `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_cred`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod`
                        ON (`pto_mod`.`id_pto_mod` = `pto_mod_detalle`.`id_pto_mod`)
                    WHERE `pto_mod`.`id_tipo_mod` IN (1,6) AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN $rango
                    GROUP BY `id_pto_mod`, `id_cargue`) AS `contracredito`
                    ON (`pto_mod`.`id_pto_mod` = `contracredito`.`id_pto_mod` AND `pto_cargue`.`id_cargue` = `contracredito`.`id_cargue`)
            WHERE `pto_mod`.`estado` = 2 AND (`adicion`.`valor` > 0 OR `reduccion`.`valor` > 0 OR `credito`.`valor` > 0 OR `contracredito`.`valor` > 0) AND `pto_presupuestos`.`id_tipo` = 2
            ORDER BY  DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d')";
    $res = $cmd->query($sql);
    $lista = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$body = '';
foreach ($lista as $r) {
    $acto = $r['nombre'] . ' ' . $r['numero_acto'];
    $body .= "<tr>
                <td>{$r['cod_pptal']}</td>
                <td>{$acto}</td>
                <td>{$r['fecha']}</td>
                <td>{$r['val_adicion']}</td>
                <td>{$r['val_reduccion']}</td>
                <td>{$r['val_credito']}</td>
                <td>{$r['val_contracredito']}</td>
                <td>{$r['val_aplazamiento']}</td>
                <td>{$r['val_desaplazamiento']}</td>
            </tr>";
}
echo "\xEF\xBB\xBF";
?>
<table class="table-bordered bg-light" style="width:100% !important;" border=1>
    <tr>
        <td colspan="9" style="text-align: center; font-weight: bold;">MODIFICACIONES AL PRESUPUESTO DE GASTOS</td>
    </tr>
    <tr>
        <td colspan="9" style="text-align: center; font-weight: bold;">AÑO: <?= $vigencia; ?></td>
    </tr>
    <tr>
        <td colspan="9" style="text-align: center; font-weight: bold;">PERIODO: <?= $meses ?></td>
    </tr>
    <tr>
        <th>Código Rubro Presupuestal</th>
        <th>Acto Administrativo</th>
        <th>Fecha</th>
        <th>Adición</th>
        <th>Reducción</th>
        <th>Crédito</th>
        <th>Contracrédito</th>
        <th>Aplazamiento</th>
        <th>Desaplazamiento</th>
    </tr>
    <tbody>
        <?= $body; ?>
    </tbody>
</table>