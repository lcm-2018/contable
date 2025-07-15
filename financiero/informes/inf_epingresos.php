<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

header("Content-type: application/vnd.ms-excel charset=utf-8");
header("Content-Disposition: attachment; filename=Ejecucion_presupuestal_ingresos.xls");
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
                , `pto_cargue`.`valor_aprobado`
                , IFNULL(`adicion`.`debito`,0) AS `add`
                , IFNULL(`reduccion`.`credito`,0) AS `red`
                , IFNULL(`ad_periodo`.`valor`,0) AS `add_periodo`
                , IFNULL(`ad_acumulado`.`valor`,0) AS `add_acumulado`
                , IFNULL(`red_periodo`.`valor`,0) AS `red_periodo`
                , IFNULL(`red_acumulado`.`valor`,0) AS `red_acumulado`
                , IFNULL(`rec_periodo`.`valor`,0) AS `rec_periodo`
                , IFNULL(`rec_acumulado`.`valor`,0) AS `rec_acumulado`
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
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_deb`) AS `debito`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                        ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`id_tipo_mod` IN (1,2,6) AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN '$vigencia-01-01' AND '$vigencia-05-31' AND `pto_mod`.`estado` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `adicion`
                        ON (`adicion`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN 
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_cred`) AS `credito`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                        ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`id_tipo_mod` IN (1,3,6) AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN '$vigencia-01-01' AND '$vigencia-05-31' AND `pto_mod`.`estado` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `reduccion`
                        ON (`reduccion`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN 
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_deb`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                        ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`id_tipo_mod` IN (1,2,6) AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN $rango AND `pto_mod`.`estado` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `ad_periodo`
                        ON (`ad_periodo`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN 
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_deb`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                        ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`id_tipo_mod` IN (1,2,6) AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN '$vigencia-01-01' AND '$vigencia-12-31' AND `pto_mod`.`estado` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `ad_acumulado`
                        ON (`ad_acumulado`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN 
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_cred`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                        ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`id_tipo_mod` IN (1,3,6) AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN $rango AND `pto_mod`.`estado` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `red_periodo`
                        ON (`red_periodo`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN 
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_cred`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                        ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`id_tipo_mod` IN (1,3,6) AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN '$vigencia-01-01' AND '$vigencia-12-31' AND `pto_mod`.`estado` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `red_acumulado`
                        ON (`red_acumulado`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        CASE
                        WHEN `pto_rec_detalle`.`id_rubro` IS NULL THEN `pto_rad_detalle`.`id_rubro` 
                        ELSE `pto_rec_detalle`.`id_rubro`
                        END AS `id_rubro` 
                        , SUM(IFNULL(`pto_rec_detalle`.`valor`,0) - IFNULL(`pto_rec_detalle`.`valor_liberado`,0)) AS `valor`
                    FROM
                        `pto_rec_detalle`
                        INNER JOIN `pto_rec` 
                        ON (`pto_rec_detalle`.`id_pto_rac` = `pto_rec`.`id_pto_rec`)
                        LEFT JOIN `pto_rad_detalle` 
                        ON (`pto_rec_detalle`.`id_pto_rad_detalle` = `pto_rad_detalle`.`id_pto_rad_det`)
                    WHERE (DATE_FORMAT(`pto_rec`.`fecha`,'%Y-%m-%d') BETWEEN $rango AND `pto_rec`.`estado` = 2)
                    GROUP BY `id_rubro`) AS `rec_periodo`
                        ON (`rec_periodo`.`id_rubro` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        CASE
                        WHEN `pto_rec_detalle`.`id_rubro` IS NULL THEN `pto_rad_detalle`.`id_rubro` 
                        ELSE `pto_rec_detalle`.`id_rubro`
                        END AS `id_rubro` 
                        , SUM(IFNULL(`pto_rec_detalle`.`valor`,0) - IFNULL(`pto_rec_detalle`.`valor_liberado`,0)) AS `valor`
                    FROM
                        `pto_rec_detalle`
                        INNER JOIN `pto_rec` 
                        ON (`pto_rec_detalle`.`id_pto_rac` = `pto_rec`.`id_pto_rec`)
                        LEFT JOIN `pto_rad_detalle` 
                        ON (`pto_rec_detalle`.`id_pto_rad_detalle` = `pto_rad_detalle`.`id_pto_rad_det`)
                    WHERE (DATE_FORMAT(`pto_rec`.`fecha`,'%Y-%m-%d') BETWEEN '$vigencia-01-01' AND '$vigencia-12-31' AND `pto_rec`.`estado` = 2)
                    GROUP BY `id_rubro`) AS `rec_acumulado`
                        ON (`rec_acumulado`.`id_rubro` = `pto_cargue`.`id_cargue`)
            WHERE (`pto_presupuestos`.`id_tipo` = 1 AND `pto_presupuestos`.`id_vigencia` = $id_vigencia)
            ORDER BY `pto_cargue`.`id_cargue` ASC";
    $res = $cmd->query($sql);
    $lista = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$body = '';
foreach ($lista as $r) {
    if ($periodo == 2) {
        $valor = $r['valor_aprobado'] + $r['add'] - $r['red'];
    } else {
        $valor = $r['valor_aprobado'];
    }
    $body .=
        <<<HTML
            <tr>
                <td>{$r['cod_rubro']}</td>
                <td>{$r['nom_rubro']}</td>
                <td>{$valor}</td>
                <td>{$r['add_periodo']}</td>
                <td>{$r['add_acumulado']}</td>
                <td>{$r['red_periodo']}</td>
                <td>{$r['red_acumulado']}</td>
                <td>{$r['rec_periodo']}</td>
                <td>{$r['rec_acumulado']}</td>
                <td>{$meses}</td>
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
        <th>Código Rubro Presupuestal</th>
        <th>Nombre Rubro Presupuestal</th>
        <th>Presupuesto Inicial</th>
        <th>Adiciones periodo</th>
        <th>Adiciones acumuladas</th>
        <th>Reducciones periodo</th>
        <th>Reducciones acumuladas</th>
        <th>Recaudos periodo</th>
        <th>Recaudos acumulados</th>
        <th>Periodo reportado</th>
    </tr>
    <tbody>
        <?= $body; ?>
    </tbody>
</table>