<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

header("Content-type: application/vnd.ms-excel charset=utf-8");
header("Content-Disposition: attachment; filename=Ejecucion_presupuestal_gastos.xls");
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
                , IFNULL(`cre_periodo`.`valor`,0) AS `cre_periodo`
                , IFNULL(`cre_acumulado`.`valor`,0) AS `cre_acumulado`
                , IFNULL(`contra_periodo`.`valor`,0) AS `contra_periodo`
                , IFNULL(`contra_acumulado`.`valor`,0) AS `contra_acumulado`
                , 0 AS `aplazamiento`
                , 0 AS `aplazamiento_acum`
                , 0 AS `desaplazamiento`
                , 0 AS `desaplazamiento_acum`
                , IFNULL(`red_periodo`.`valor`,0) AS `red_periodo`
                , IFNULL(`red_acumulado`.`valor`,0) AS `red_acumulado`
                , IFNULL(`add_periodo`.`valor`,0) AS `add_periodo`
                , IFNULL(`add_acumulado`.`valor`,0) AS `add_acumulado`
                , IFNULL(`compromiso_periodo`.`valor`,0) AS `compromiso_periodo`
                , IFNULL(`compromiso_acumulado`.`valor`,0) AS `compromiso_acumulado`
                , IFNULL(`pago_periodo`.`valor`,0) AS `pago_periodo`
                , IFNULL(`pago_acumulado`.`valor`,0) AS `pago_acumulado`
            FROM
                `pto_cargue`
                INNER JOIN `pto_presupuestos` 
                    ON (`pto_cargue`.`id_pto` = `pto_presupuestos`.`id_pto`)
                INNER JOIN `pto_homologa_gastos` 
                    ON (`pto_homologa_gastos`.`id_cargue` = `pto_cargue`.`id_cargue`)
                INNER JOIN `pto_sia` 
                    ON (`pto_homologa_gastos`.`id_sia` = `pto_sia`.`id_sia`)
                LEFT JOIN 
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_deb`) AS `debito`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                        ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`id_tipo_mod` IN (2) AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN '$vigencia-01-01' AND '$vigencia-05-31' AND `pto_mod`.`estado` = 2)
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
                    WHERE (`pto_mod`.`id_tipo_mod` IN (3) AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN '$vigencia-01-01' AND '$vigencia-05-31' AND `pto_mod`.`estado` = 2)
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
                    WHERE (`pto_mod`.`id_tipo_mod` IN (1,6) AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN $rango AND `pto_mod`.`estado` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `cre_periodo`
                        ON (`cre_periodo`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN 
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_deb`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                        ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`id_tipo_mod` IN (1,6) AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN $rango AND `pto_mod`.`estado` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `cre_acumulado`
                        ON (`cre_acumulado`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN 
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_cred`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                        ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`id_tipo_mod` IN (1,3,6) AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN $rango AND `pto_mod`.`estado` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `contra_periodo`
                        ON (`contra_periodo`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN 
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_cred`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                        ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`id_tipo_mod` IN (1,3,6) AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN $rango AND `pto_mod`.`estado` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `contra_acumulado`
                        ON (`contra_acumulado`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(IFNULL(`pto_mod_detalle`.`valor_cred`,0)) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                            ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`id_tipo_mod` = 3 AND `pto_mod`.`fecha` BETWEEN $rango AND `pto_mod`.`estado` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `red_periodo`
                        ON (`red_periodo`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(IFNULL(`pto_mod_detalle`.`valor_cred`,0)) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                            ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`id_tipo_mod` = 3 AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN $rango AND `pto_mod`.`estado` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `red_acumulado`
                        ON (`red_acumulado`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(IFNULL(`pto_mod_detalle`.`valor_cred`,0)) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                            ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`id_tipo_mod` = 2 AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN $rango AND `pto_mod`.`estado` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `add_periodo`
                        ON (`add_periodo`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(IFNULL(`pto_mod_detalle`.`valor_cred`,0)) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                            ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`id_tipo_mod` = 2 AND DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN $rango AND `pto_mod`.`estado` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `add_acumulado`
                        ON (`add_acumulado`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT 
                        `t1`.`id_rubro`
                        , IFNULL(`t1`.`valor`,0) - IFNULL(`t2`.`valor`,0) AS `valor`
                    FROM
                        (SELECT
                            `pto_cdp_detalle`.`id_rubro`
                            , SUM(IFNULL(`pto_crp_detalle`.`valor`,0)) AS `valor`
                        FROM
                            `pto_crp_detalle`
                            INNER JOIN `pto_crp` 
                                ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                            INNER JOIN `pto_cdp_detalle` 
                                ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                        WHERE (DATE_FORMAT(`pto_crp`.`fecha`,'%Y-%m-%d') BETWEEN $rango AND `pto_crp`.`estado` = 2)
                        GROUP BY `pto_cdp_detalle`.`id_rubro`) AS `t1`
                        LEFT JOIN
                            (SELECT
                                `pto_cdp_detalle`.`id_rubro`
                                , SUM(IFNULL(`pto_crp_detalle`.`valor_liberado`,0)) AS `valor`
                            FROM
                                `pto_crp_detalle`
                                INNER JOIN `pto_crp` 
                                    ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                                INNER JOIN `pto_cdp_detalle` 
                                    ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                            WHERE (`pto_crp_detalle`.`fecha_libera` BETWEEN $rango AND `pto_crp`.`estado` = 2)
                            GROUP BY `pto_cdp_detalle`.`id_rubro`) AS `t2`
                            ON (`t2`.`id_rubro` = `t1`.`id_rubro`)) AS `compromiso_periodo`
                    ON (`pto_cargue`.`id_cargue` = `compromiso_periodo`.`id_rubro`)
                LEFT JOIN
                    (SELECT 
                        `t1`.`id_rubro`
                        , IFNULL(`t1`.`valor`,0) - IFNULL(`t2`.`valor`,0) AS `valor`
                    FROM
                        (SELECT
                            `pto_cdp_detalle`.`id_rubro`
                            , SUM(IFNULL(`pto_crp_detalle`.`valor`,0)) AS `valor`
                        FROM
                            `pto_crp_detalle`
                            INNER JOIN `pto_crp` 
                                ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                            INNER JOIN `pto_cdp_detalle` 
                                ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                        WHERE (DATE_FORMAT(`pto_crp`.`fecha`,'%Y-%m-%d') BETWEEN $rango AND `pto_crp`.`estado` = 2)
                        GROUP BY `pto_cdp_detalle`.`id_rubro`) AS `t1`
                        LEFT JOIN
                            (SELECT
                                `pto_cdp_detalle`.`id_rubro`
                                , SUM(IFNULL(`pto_crp_detalle`.`valor_liberado`,0)) AS `valor`
                            FROM
                                `pto_crp_detalle`
                                INNER JOIN `pto_crp` 
                                    ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                                INNER JOIN `pto_cdp_detalle` 
                                    ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                            WHERE (DATE_FORMAT(`pto_crp_detalle`.`fecha_libera`,'%Y-%m-%d') BETWEEN $rango AND `pto_crp`.`estado` = 2)
                            GROUP BY `pto_cdp_detalle`.`id_rubro`) AS `t2`
                            ON (`t2`.`id_rubro` = `t1`.`id_rubro`)) AS `compromiso_acumulado`
                    ON (`pto_cargue`.`id_cargue` = `compromiso_acumulado`.`id_rubro`)
                LEFT JOIN
                    (SELECT
                        `pto_cdp_detalle`.`id_rubro`
                        , SUM(`pto_pag_detalle`.`valor`) AS `valor`
                    FROM
                        `pto_pag_detalle`
                        INNER JOIN `ctb_doc` 
                            ON (`pto_pag_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        INNER JOIN `pto_cop_detalle` 
                            ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                        INNER JOIN `pto_crp_detalle` 
                            ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                        INNER JOIN `pto_cdp_detalle` 
                            ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                    WHERE (DATE_FORMAT(`ctb_doc`.`fecha`,'%Y-%m-%d') BETWEEN $rango AND `ctb_doc`.`estado` = 2)
                    GROUP BY `pto_cdp_detalle`.`id_rubro`) AS `pago_periodo`
                        ON (`pto_cargue`.`id_cargue` = `pago_periodo`.`id_rubro`)
                LEFT JOIN
                    (SELECT
                        `pto_cdp_detalle`.`id_rubro`
                        , SUM(`pto_pag_detalle`.`valor`) AS `valor`
                    FROM
                        `pto_pag_detalle`
                        INNER JOIN `ctb_doc` 
                            ON (`pto_pag_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        INNER JOIN `pto_cop_detalle` 
                            ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                        INNER JOIN `pto_crp_detalle` 
                            ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                        INNER JOIN `pto_cdp_detalle` 
                            ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                    WHERE (DATE_FORMAT(`ctb_doc`.`fecha`,'%Y-%m-%d') BETWEEN $rango AND `ctb_doc`.`estado` = 2)
                    GROUP BY `pto_cdp_detalle`.`id_rubro`) AS `pago_acumulado`
                        ON (`pto_cargue`.`id_cargue` = `pago_acumulado`.`id_rubro`)
            WHERE (`pto_presupuestos`.`id_tipo` = 2 AND `pto_presupuestos`.`id_vigencia` = $id_vigencia)
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
    $body .= "<tr>
                <td>{$r['cod_rubro']}</td>
                <td>{$r['nom_rubro']}</td>
                <td>{$valor}</td>
                <td>{$r['cre_periodo']}</td>
                <td>{$r['cre_acumulado']}</td>
                <td>{$r['contra_periodo']}</td>
                <td>{$r['contra_acumulado']}</td>
                <td>{$r['aplazamiento']}</td>
                <td>{$r['aplazamiento_acum']}</td>
                <td>{$r['desaplazamiento']}</td>
                <td>{$r['desaplazamiento_acum']}</td>
                <td>{$r['red_periodo']}</td>
                <td>{$r['red_acumulado']}</td>
                <td>{$r['add_periodo']}</td>
                <td>{$r['add_acumulado']}</td>
                <td>{$r['compromiso_periodo']}</td>
                <td>{$r['compromiso_acumulado']}</td>
                <td>{$r['pago_periodo']}</td>
                <td>{$r['pago_acumulado']}</td>
                <td>{$meses}</td>
            </tr>";
}
echo "\xEF\xBB\xBF";
?>
<table class="table-bordered bg-light" style="width:100% !important;" border=1>
    <tr>
        <td colspan="20" style="text-align: center; font-weight: bold;">EJECUCIÓN PRESUPUESTAL DE GASTOS</td>
    </tr>
    <tr>
        <td colspan="20" style="text-align: center; font-weight: bold;">AÑO: <?= $vigencia; ?></td>
    </tr>
    <tr>
        <td colspan="20" style="text-align: center; font-weight: bold;">PERIODO: <?= $meses ?></td>
    </tr>
    <tr>
        <th>Código Rubro Presupuestal</th>
        <th>Nombre Rubro Presupuestal</th>
        <th>Apropiación Inicial</th>
        <th>Crédito</th>
        <th>Crédito Acum</th>
        <th>Contracréditos</th>
        <th>Contracréditos Acum</th>
        <th>Aplazamientos</th>
        <th>Aplazamientos Acum</th>
        <th>Desaplazamientos</th>
        <th>Desaplazamientos Acum</th>
        <th>Reducciones</th>
        <th>Reducciones Acum</th>
        <th>Adiciones</th>
        <th>Adiciones Acum</th>
        <th>Compromisos Registro Presupuestal</th>
        <th>Compromisos Acum</th>
        <th>Pagos</th>
        <th>Pagos Acum</th>
        <th>Periodo reportado</th>
    </tr>
    <tbody>
        <?= $body; ?>
    </tbody>
</table>