<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

header("Content-type: application/vnd.ms-excel charset=utf-8");
header("Content-Disposition: attachment; filename=Movimeinto_Bancos.xls");
header("Pragma: no-cache");
header("Expires: 0");

include '../../conexion.php';
$periodo = $_POST['periodo'];
$vigencia = $_SESSION['vigencia'];
$vig_ant = $vigencia - 1;
$meses = '';
if ($periodo == 1) {
    $periodos = "SELECT '$vigencia-01-01' AS `mes`
                UNION ALL SELECT '$vigencia-02-01'
                UNION ALL SELECT '$vigencia-03-01'
                UNION ALL SELECT '$vigencia-04-01'
                UNION ALL SELECT '$vigencia-05-01'
                UNION ALL SELECT '$vigencia-06-01'";
    $meses = 'ENERO - JUNIO';
} else if ($periodo == 2) {
    $periodos = "SELECT '$vigencia-07-01' AS `mes`
                UNION ALL SELECT '$vigencia-08-01'
                UNION ALL SELECT '$vigencia-09-01'
                UNION ALL SELECT '$vigencia-10-01'
                UNION ALL SELECT '$vigencia-11-01'
                UNION ALL SELECT '$vigencia-12-01'";
    $meses = 'JULIO - DICIEMBRE';
} else {
    $periodos = "SELECT '$vigencia-01-01' AS `mes`
                UNION ALL SELECT '$vigencia-02-01'
                UNION ALL SELECT '$vigencia-03-01'
                UNION ALL SELECT '$vigencia-04-01'
                UNION ALL SELECT '$vigencia-05-01'
                UNION ALL SELECT '$vigencia-06-01'
                UNION ALL SELECT '$vigencia-07-01'
                UNION ALL SELECT '$vigencia-08-01'
                UNION ALL SELECT '$vigencia-09-01'
                UNION ALL SELECT '$vigencia-10-01'
                UNION ALL SELECT '$vigencia-11-01'
                UNION ALL SELECT '$vigencia-12-01'";
    $meses = 'ENERO - DICIEMBRE';
}

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT
                `ctb_pgcp`.`cuenta` AS `codigo`
                , `tb_bancos`.`cod_sia` AS `banco`
                , `tes_cuentas`.`numero`
                , `tes_cuentas`.`nombre` AS `denominacion`
                , `fin_cod_fuente`.`codigo` AS `fuente`
                , `nom_meses`.`nom_mes`
                , `tt`.`saldo`
                , IFNULL(`tesc`.`saldo`,0) AS `extr_inicial`
                , IFNULL(`taux`.`debito`,0) AS `debito`
                , IFNULL(`taux`.`credito`,0) AS `credito`
                , 0 AS `nd`
                , 0 AS `nc`
                , IFNULL(`tt2`.`saldo`,0) AS `sf_libros`
                , IFNULL(`tes_conciliacion`.`saldo_extracto`,0) AS `sf_extracto`
                , IFNULL(`ttt`.`debito`,0) AS `sf_debito`
                , IFNULL(`ttt`.`credito`,0) AS `sf_credito`
            FROM
                `tes_cuentas`
                INNER JOIN `ctb_pgcp` 
                    ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                INNER JOIN `tb_bancos` 
                    ON (`tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)
                INNER JOIN `fin_cod_fuente` 
                    ON (`tes_cuentas`.`id_fte` = `fin_cod_fuente`.`id`)
                INNER JOIN
                    (SELECT 
                        DATE_FORMAT(`meses`.`mes`, '%m') AS `mes`,
                        `ctb_libaux`.`id_cuenta`,
                        SUM(IFNULL(`ctb_libaux`.`debito`, 0) - IFNULL(`ctb_libaux`.`credito`, 0)) AS `saldo`
                    FROM 
                        ($periodos) AS `meses`
                        JOIN `ctb_doc` 
                            ON `ctb_doc`.`estado` = 2 AND DATE_FORMAT(`ctb_doc`.`fecha`,'%Y-%m-%d') < `meses`.`mes`
                        JOIN `ctb_libaux` 
                            ON `ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`
                    GROUP BY `mes`, `ctb_libaux`.`id_cuenta`
                    ORDER BY `mes`, `ctb_libaux`.`id_cuenta`) AS `tt`
                    ON (`tt`.`id_cuenta` = `tes_cuentas`.`id_cuenta`)
                LEFT JOIN `nom_meses`
                    ON(`nom_meses`.`codigo` = `tt`.`mes`)
                LEFT JOIN 
                    (SELECT 
                        `tes_cuentas`.`id_tes_cuenta`
                        , LPAD(
                            CASE 
                                WHEN `mc`.`mes` = 12 THEN 1
                                ELSE `mc`.`mes` + 1
                            END, 2, 
                            '0') AS `mes`
                        , IFNULL(`saldo_extracto`, 0) AS `saldo`
                            
                    FROM 
                        `tes_cuentas`
                        JOIN
                            (SELECT '$vig_ant' AS `vigencia`,'12'  AS `mes`
                            UNION ALL SELECT '$vigencia','01'
                            UNION ALL SELECT '$vigencia','02'
                            UNION ALL SELECT '$vigencia','03'
                            UNION ALL SELECT '$vigencia','04'
                            UNION ALL SELECT '$vigencia','05'
                            UNION ALL SELECT '$vigencia','06'
                            UNION ALL SELECT '$vigencia','07'
                            UNION ALL SELECT '$vigencia','08'
                            UNION ALL SELECT '$vigencia','09'
                            UNION ALL SELECT '$vigencia','10'
                            UNION ALL SELECT '$vigencia','11'
                            ) AS `mc`
                        LEFT JOIN `tes_conciliacion` AS `tc`
                            ON (`tc`.`vigencia` = `mc`.`vigencia` AND `mc`.`mes`=`tc`.`mes` AND`tes_cuentas`.`id_tes_cuenta`= `tc`.`id_cuenta`)
                    WHERE `tes_cuentas`.`estado` = 1
                    ORDER BY `tes_cuentas`.`id_tes_cuenta`, `mes`) AS `tesc`
                    ON (`tesc`.`mes` = `tt`.`mes` AND `tesc`.`id_tes_cuenta` = `tes_cuentas`.`id_tes_cuenta`)
                LEFT JOIN 
                    (SELECT
                        DATE_FORMAT(`ctb_doc`.`fecha`,'%m') AS `mes`
                        , SUM(IFNULL(`ctb_libaux`.`debito`,0)) AS `debito`
                        , SUM(IFNULL(`ctb_libaux`.`credito`,0)) AS `credito`  
                        , `ctb_libaux`.`id_cuenta`
                        
                    FROM
                        `ctb_libaux`
                        INNER JOIN `ctb_doc` 
                        ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    WHERE (`ctb_doc`.`estado` = 2 AND DATE_FORMAT(`ctb_doc`.`fecha`,'%Y-%m-%d') BETWEEN '$vigencia-01-01' AND '$vigencia-12-31')
                    GROUP BY DATE_FORMAT(`ctb_doc`.`fecha`,'%Y-%m'),`ctb_libaux`.`id_cuenta`)AS `taux`
                    ON (`taux`.`mes` = `tt`.`mes` AND `taux`.`id_cuenta` = `tes_cuentas`.`id_cuenta`)
                LEFT JOIN
                    (SELECT 
                        DATE_FORMAT(`meses`.`mes`, '%m') AS `mes`,
                        `ctb_libaux`.`id_cuenta`,
                        SUM(IFNULL(`ctb_libaux`.`debito`, 0) - IFNULL(`ctb_libaux`.`credito`, 0)) AS `saldo`
                    FROM 
                        ($periodos) AS `meses`
                        JOIN `ctb_doc` 
                            ON `ctb_doc`.`estado` = 2 AND DATE_FORMAT(`ctb_doc`.`fecha`,'%Y-%m') <= DATE_FORMAT(`meses`.`mes`,'%Y-%m')
                        JOIN `ctb_libaux` 
                            ON `ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`
                    GROUP BY `mes`, `ctb_libaux`.`id_cuenta`
                    ORDER BY `mes`, `ctb_libaux`.`id_cuenta`) AS `tt2`
                    ON (`tt2`.`id_cuenta` = `tes_cuentas`.`id_cuenta` AND `nom_meses`.`codigo` = `tt2`.`mes`)
                LEFT JOIN `tes_conciliacion`
                    ON (`tes_conciliacion`.`vigencia` = '$vigencia' AND `tes_conciliacion`.`mes` = `tt2`.`mes` AND `tes_conciliacion`.`id_cuenta` = `tes_cuentas`.`id_tes_cuenta`)
                LEFT JOIN
                    (SELECT 
                        DATE_FORMAT(`mes`, '%m') AS `mes`, `id_cuenta`, `debito`, `credito`
                    FROM
                        (SELECT 
                            CONCAT(`mc`.`mes`,'-01') AS `mes`,
                            `cl`.`id_cuenta`,
                            SUM(`cl`.`debito`) AS `debito`,
                            SUM(`cl`.`credito`) AS `credito`
                        FROM 
                            (SELECT '$vigencia-01' AS `mes`
                            UNION ALL SELECT '$vigencia-02'
                            UNION ALL SELECT '$vigencia-03'
                            UNION ALL SELECT '$vigencia-04'
                            UNION ALL SELECT '$vigencia-05'
                            UNION ALL SELECT '$vigencia-06'
                            UNION ALL SELECT '$vigencia-07'
                            UNION ALL SELECT '$vigencia-08'
                            UNION ALL SELECT '$vigencia-09'
                            UNION ALL SELECT '$vigencia-10'
                            UNION ALL SELECT '$vigencia-11'
                            UNION ALL SELECT '$vigencia-12') AS `mc`
                            JOIN 
                            (SELECT
                                DATE_FORMAT(`cd`.`fecha`, '%Y-%m') AS `fecha`,
                                `cl`.`id_cuenta`,
                                IFNULL(`cl`.`debito`, 0) AS `debito`,
                                IFNULL(`cl`.`credito`, 0) AS `credito`
                                FROM `ctb_libaux` `cl`
                                INNER JOIN `ctb_doc` `cd` ON `cl`.`id_ctb_doc` = `cd`.`id_ctb_doc`
                                LEFT JOIN `tes_conciliacion_detalle` `tcd` ON `tcd`.`id_ctb_libaux` = `cl`.`id_ctb_libaux`
                                WHERE `cd`.`estado` = 2 AND `tcd`.`id_ctb_libaux` IS NULL) AS `cl` 
                                ON `cl`.`fecha` <= `mc`.`mes`
                        GROUP BY `mc`.`mes`, `cl`.`id_cuenta`
                        ORDER BY `cl`.`id_cuenta`, `mc`.`mes`) AS `acum`)`ttt`
                        ON (`ttt`.`id_cuenta` = `tes_cuentas`.`id_tes_cuenta` AND `ttt`.`mes` = `tt2`.`mes`)
            WHERE (`tes_cuentas`.`estado` = 1)";
    $res = $cmd->query($sql);
    $lista = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$body = '';
foreach ($lista as $r) {
    $saldo_con = $r['sf_libros'] - ($r['sf_extracto'] + $r['sf_debito'] - $r['sf_credito']);
    $body .="<tr>
                <td>{$r['codigo']}</td>
                <td>{$r['banco']}</td>
                <td>{$r['numero']}</td>
                <td>{$r['denominacion']}</td>
                <td>{$r['fuente']}</td>
                <td>{$r['nom_mes']}</td>
                <td>{$r['saldo']}</td>
                <td>{$r['extr_inicial']}</td>
                <td>{$r['debito']}</td>
                <td>{$r['credito']}</td>
                <td>{$r['nd']}</td>
                <td>{$r['nc']}</td>
                <td>{$r['sf_libros']}</td>
                <td>{$r['sf_extracto']}</td>
                <td>{$saldo_con}</td>
            </tr>";
}
echo "\xEF\xBB\xBF";
?>
<table class="table-bordered bg-light" style="width:100% !important;" border=1>
    <tr>
        <td colspan="15" style="text-align: center; font-weight: bold;">INFORME DE BANCOS</td>
    </tr>
    <tr>
        <td colspan="15" style="text-align: center; font-weight: bold;">AÑO: <?= $vigencia; ?></td>
    </tr>
    <tr>
        <td colspan="15" style="text-align: center; font-weight: bold;">PERIODO: <?= $meses ?></td>
    </tr>
    <tr>
        <th>Código Contable</th>
        <th>Banco</th>
        <th>No. Cuenta</th>
        <th>Denominación</th>
        <th>Fte. Finciación</th>
        <th>Mes</th>
        <th>Saldo Inicial Libros</th>
        <th>Saldo Inicial Extracto</th>
        <th>Ingresos</th>
        <th>Egresos</th>
        <th>Notas Débito</th>
        <th>Notas Crédito</th>
        <th>Saldo Final Libros</th>
        <th>Saldo Final Extracto</th>
        <th>Saldo Conciliado</th>
    </tr>
    <tbody>
        <?= $body; ?>
    </tbody>
</table>