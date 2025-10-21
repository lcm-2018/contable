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
$inicia = $vigencia . '-01-01';
$meses = '';
if ($periodo == '03') {
    $rango = "'$inicia' AND '$vigencia-03-31'";
    $meses = 'MARZO';
} else if ($periodo == '06') {
    $rango = "'$inicia' AND '$vigencia-06-30'";
    $meses = 'JUNIO';
} else if ($periodo == '09') {
    $rango = "'$inicia' AND '$vigencia-09-30'";
    $meses = 'SEPTIEMBRE';
} else if ($periodo == '12') {
    $rango = "'$inicia' AND '$vigencia-12-31'";
    $meses = 'DICIEMBRE';
}

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "WITH
                `taux` AS 
                    (SELECT 	
                        `pto_homologa_ingresos`.`id_siho`
                        , SUM(`vals`.`valor` - `vals`.`valor_liberado`) `valor`
                        , `vals`.`mes`
                    FROM
                        (SELECT
                            IFNULL(`pto_rec_detalle`.`id_rubro`, `pto_rad_detalle`.`id_rubro`) AS `id_rubro`
                            , `pto_rec_detalle`.`valor`
                            , `pto_rec_detalle`.`valor_liberado`
                            , DATE_FORMAT(`pto_rec`.`fecha`,'%m') AS `mes`
                        FROM
                            `pto_rec_detalle`
                            INNER JOIN `pto_rec` 
                            ON (`pto_rec_detalle`.`id_pto_rac` = `pto_rec`.`id_pto_rec`)
                            INNER JOIN `pto_rad_detalle` 
                            ON (`pto_rec_detalle`.`id_pto_rad_detalle` = `pto_rad_detalle`.`id_pto_rad_det`)
                        WHERE (DATE_FORMAT(`pto_rec`.`fecha`,'%Y-%m-%d') BETWEEN  '$vigencia-01-01' AND '$vigencia-12-31' AND `pto_rec`.`estado` = 2)) AS `vals`
                        INNER JOIN `pto_homologa_ingresos`
                            ON (`pto_homologa_ingresos`.`id_cargue` = `vals`.`id_rubro`)
                    GROUP BY `pto_homologa_ingresos`.`id_siho`, `vals`.`mes`)

            SELECT 
                DISTINCT(`pto_homologa_ingresos`.`id_siho`) AS `id_siho`
                , `pto_siho`.`nombre`
                , (IFNULL(`ppto`.`inicial`,0) +IFNULL(`add`.`debito`,0) -IFNULL(`red`.`credito`,0)) AS `definitivo`  
                , IFNULL(`rec`.`valor`,0) AS `reconocimiento`
                , IFNULL(`enero`.`valor`,0) AS `val_enero` 
                , IFNULL(`febrero`.`valor`,0) AS `val_febrero`
                , IFNULL(`marzo`.`valor`,0) AS `val_marzo`
                , IFNULL(`abril`.`valor`,0) AS `val_abril`
                , IFNULL(`mayo`.`valor`,0) AS `val_mayo`
                , IFNULL(`junio`.`valor`,0) AS `val_junio`
                , IFNULL(`julio`.`valor`,0) AS `val_julio`
                , IFNULL(`agosto`.`valor`,0) AS `val_agosto`
                , IFNULL(`septiembre`.`valor`,0) AS `val_septiembre`
                , IFNULL(`octubre`.`valor`,0) AS `val_octubre`
                , IFNULL(`noviembre`.`valor`,0) AS `val_noviembre`
                , IFNULL(`diciembre`.`valor`,0) AS `val_diciembre`
            FROM
                `pto_homologa_ingresos`
                INNER JOIN `pto_siho`
                    ON (`pto_siho`.`id_siho` = `pto_homologa_ingresos`.`id_siho`)
                INNER JOIN
                    (SELECT
                        `pto_homologa_ingresos`.`id_siho`
                        , SUM(`pto_cargue`.`valor_aprobado`) AS `inicial`
                    FROM
                        `pto_homologa_ingresos`
                        INNER JOIN `pto_cargue` 
                        ON (`pto_homologa_ingresos`.`id_cargue` = `pto_cargue`.`id_cargue`)
                    GROUP BY `pto_homologa_ingresos`.`id_siho`) AS `ppto`
                    ON (`ppto`.`id_siho` = `pto_homologa_ingresos`.`id_siho`)
                LEFT JOIN
                    (SELECT
                        `pto_homologa_ingresos`.`id_siho`
                        , SUM(`pto_mod_detalle`.`valor_deb`) AS `debito`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                        INNER JOIN `pto_homologa_ingresos` ON (`pto_mod_detalle`.`id_cargue` = `pto_homologa_ingresos`.`id_cargue`)
                    WHERE (DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN  $rango AND `pto_mod`.`id_tipo_mod` IN (1,2,6) AND `pto_mod`.`estado` = 2)
                    GROUP BY `pto_homologa_ingresos`.`id_siho`) AS `add`
                    ON (`pto_homologa_ingresos`.`id_siho` = `add`.`id_siho`)
                LEFT JOIN
                    (SELECT
                        `pto_homologa_ingresos`.`id_siho`
                        , SUM(`pto_mod_detalle`.`valor_cred`) AS `credito`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                        INNER JOIN `pto_homologa_ingresos` ON (`pto_homologa_ingresos`.`id_cargue` = `pto_mod_detalle`.`id_cargue`)
                    WHERE (DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN  $rango AND `pto_mod`.`id_tipo_mod` IN (1,3,6) AND `pto_mod`.`estado` = 2)
                    GROUP BY `pto_homologa_ingresos`.`id_siho`) AS `red`
                    ON (`pto_homologa_ingresos`.`id_siho` = `red`.`id_siho`)
                LEFT JOIN
                    (SELECT
                        `pto_homologa_ingresos`.`id_siho`
                        , SUM(`pto_rad_detalle`.`valor` -`pto_rad_detalle`.`valor_liberado`) AS `valor`
                    FROM
                        `pto_rad_detalle`
                        INNER JOIN `pto_rad` 
                        ON (`pto_rad_detalle`.`id_pto_rad` = `pto_rad`.`id_pto_rad`)
                        INNER JOIN `pto_homologa_ingresos` 
                        ON (`pto_rad_detalle`.`id_rubro` = `pto_homologa_ingresos`.`id_cargue`)
                    WHERE (DATE_FORMAT(`pto_rad`.`fecha`,'%Y-%m-%d') BETWEEN  $rango AND `pto_rad`.`estado` = 2)
                    GROUP BY `pto_homologa_ingresos`.`id_siho`) AS `rec`
                    ON (`pto_homologa_ingresos`.`id_siho` = `rec`.`id_siho`)
                LEFT JOIN `taux` AS `enero` ON `enero`.`id_siho` = `pto_homologa_ingresos`.`id_siho` AND `enero`.`mes` = '01'
                LEFT JOIN `taux` AS `febrero` ON `febrero`.`id_siho` = `pto_homologa_ingresos`.`id_siho` AND `febrero`.`mes` = '02'
                LEFT JOIN `taux` AS `marzo` ON `marzo`.`id_siho` = `pto_homologa_ingresos`.`id_siho` AND `marzo`.`mes` = '03'
                LEFT JOIN `taux` AS `abril` ON `abril`.`id_siho` = `pto_homologa_ingresos`.`id_siho` AND `abril`.`mes` = '04'
                LEFT JOIN `taux` AS `mayo` ON `mayo`.`id_siho` = `pto_homologa_ingresos`.`id_siho` AND `mayo`.`mes` = '05'
                LEFT JOIN `taux` AS `junio` ON `junio`.`id_siho` = `pto_homologa_ingresos`.`id_siho` AND `junio`.`mes` = '06'
                LEFT JOIN `taux` AS `julio` ON `julio`.`id_siho` = `pto_homologa_ingresos`.`id_siho` AND `julio`.`mes` = '07'
                LEFT JOIN `taux` AS `agosto` ON `agosto`.`id_siho` = `pto_homologa_ingresos`.`id_siho` AND `agosto`.`mes` = '08'
                LEFT JOIN `taux` AS `septiembre` ON `septiembre`.`id_siho` = `pto_homologa_ingresos`.`id_siho` AND `septiembre`.`mes` = '09'
                LEFT JOIN `taux` AS `octubre` ON `octubre`.`id_siho` = `pto_homologa_ingresos`.`id_siho` AND `octubre`.`mes` = '10'
                LEFT JOIN `taux` AS `noviembre` ON `noviembre`.`id_siho` = `pto_homologa_ingresos`.`id_siho` AND `noviembre`.`mes` = '11'
                LEFT JOIN `taux` AS `diciembre` ON `diciembre`.`id_siho` = `pto_homologa_ingresos`.`id_siho` AND `diciembre`.`mes` = '12'";
    $res = $cmd->query($sql);
    $lista = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$body = '';
foreach ($lista as $r) {
    $total = $r['val_enero'] + $r['val_febrero'] + $r['val_marzo'] + $r['val_abril'] + $r['val_mayo'] + $r['val_junio'] + $r['val_julio'] + $r['val_agosto'] + $r['val_septiembre'] + $r['val_octubre'] + $r['val_noviembre'] + $r['val_diciembre'];
    $mas = '';
    if ($periodo == '06' || $periodo == '09' || $periodo == '12') {
        $mas .= "<td>{$r['val_abril']}</td>
                <td>{$r['val_mayo']}</td>
                <td>{$r['val_junio']}</td>";
    }
    if ($periodo == '09' || $periodo == '12') {
        $mas .= "<td>{$r['val_julio']}</td>
                <td>{$r['val_agosto']}</td>
                <td>{$r['val_septiembre']}</td>";
    }
    if ($periodo == '12') {
        $mas .= "<td>{$r['val_octubre']}</td>
                <td>{$r['val_noviembre']}</td>
                <td>{$r['val_diciembre']}</td>";
    }
    $body .= "<tr>
                <td>{$r['id_siho']}</td>
                <td>{$r['nombre']}</td>
                <td>{$r['definitivo']}</td>
                <td>{$r['reconocimiento']}</td>
                <td>{$r['val_enero']}</td>
                <td>{$r['val_febrero']}</td>
                <td>{$r['val_marzo']}</td>
                $mas
                <td>$total</td>
            </tr>";
}
$cols = 8;
$extra = '';
if ($periodo == '06' || $periodo == '09' || $periodo == '12') {
    $extra .= '<th>Abril</th>
              <th>Mayo</th>
              <th>Junio</th>';
    $cols += 3;
}
if ($periodo == '09' || $periodo == '12') {
    $extra .= '<th>Julio</th>
               <th>Agosto</th>
               <th>Septiembre</th>';
    $cols += 3;
}
if ($periodo == '12') {
    $extra .= '<th>Octubre</th>
               <th>Noviembre</th>
               <th>Diciembre</th>';
    $cols += 3;
}
echo "\xEF\xBB\xBF";
?>
<table class="table-bordered bg-light" style="width:100% !important; font-size: 12px;" border=1>
    <tr>
        <td colspan="<?= $cols; ?>" style="text-align: center; font-weight: bold;">EJECUCIÓN PRESUPUESTAL DE INGRESOS</td>
    </tr>
    <tr>
        <td colspan="<?= $cols; ?>" style="text-align: center; font-weight: bold;">AÑO: <?= $vigencia; ?></td>
    </tr>
    <tr>
        <td colspan="<?= $cols; ?>" style="text-align: center; font-weight: bold;">PERIODO: <?= $meses ?></td>
    </tr>
    <tr class="text-center">
        <th>#</th>
        <th>Nombre</th>
        <th>Definitivo</th>
        <th>Reconocimientos</th>
        <th>Enero</th>
        <th>Febrero</th>
        <th>Marzo</th>
        <?= $extra; ?>
        <th>Total</th>
    </tr>
    <tbody>
        <?= $body; ?>
    </tbody>
</table>