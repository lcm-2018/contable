<?php
session_start();
// set_time_limit(0);
// incrementar el tiempo de ejecucion del script
ini_set('max_execution_time', 5600);

include '../../conexion.php';
// Consexion a cronhis asistencial
$vigencia = $_SESSION['vigencia'];
// estraigo las variables que llegan por post en json
$fecha_inicial = $_POST['fecha_inicial'];
$fecha_corte = $_POST['fecha_final'];
$inicio = $_SESSION['vigencia'] . '-01-01';
// contar los caracteres de $cuenta_ini
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
try {
    $sql = "SELECT 
                `ctb_pgcp`.`cuenta`
                , `ctb_pgcp`.`nombre`
                , `ctb_pgcp`.`tipo_dato` AS `tipo`
                , SUM(`t1`.`debitoi`) AS `debitoi`
                , SUM(`t1`.`creditoi`) AS `creditoi`
                , SUM(`t1`.`debito`) AS `debito`
                , SUM(`t1`.`credito`) AS `credito`
            FROM
                (SELECT
                    `ctb_libaux`.`id_cuenta`
                    , SUM(`ctb_libaux`.`debito`) AS `debitoi`
                    , SUM(`ctb_libaux`.`credito`) AS `creditoi`
                    , 0 AS `debito`
                    , 0 AS `credito`
                FROM
                    `ctb_libaux`
                    INNER JOIN `ctb_doc`
                        ON `ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`
                    INNER JOIN `ctb_pgcp`
                        ON `ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`
                WHERE `ctb_doc`.`estado` = 2
                    AND ((SUBSTRING(`ctb_pgcp`.`cuenta`, 1, 1) IN ('1', '2', '3') AND `ctb_doc`.`fecha` < '$fecha_inicial')
                        OR
                    (SUBSTRING(`ctb_pgcp`.`cuenta`, 1, 1) IN ('4', '5', '6') AND `ctb_doc`.`fecha` < '$fecha_inicial' AND `ctb_doc`.`fecha` > '$inicio'))
                GROUP BY `ctb_libaux`.`id_cuenta`
                UNION ALL 
                SELECT
                    `ctb_libaux`.`id_cuenta`
                    , 0 AS `debitoi`
                    , 0 AS `creditoi`
                    , SUM(`ctb_libaux`.`debito`) AS `debito`
                    , SUM(`ctb_libaux`.`credito`) AS `credito`
                FROM
                    `ctb_libaux`
                    INNER JOIN `ctb_doc` 
                        ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    INNER JOIN `ctb_pgcp` 
                        ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                WHERE (`ctb_doc`.`fecha` BETWEEN '$fecha_inicial' AND '$fecha_corte' AND `ctb_doc`.`estado` = 2)
                GROUP BY `ctb_libaux`.`id_cuenta`) AS `t1`
                INNER JOIN `ctb_pgcp`
                    ON `t1`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`
            GROUP BY `t1`.`id_cuenta`
        ORDER BY `ctb_pgcp`.`cuenta` ASC";
    $res = $cmd->query($sql);
    $datos = $res->fetchAll();
} catch (Exception $e) {
    echo $e->getMessage();
}
try {
    $sql = "SELECT `cuenta`,`nombre`, `id_pgcp`,`tipo_dato` FROM `ctb_pgcp` WHERE (`estado` = 1)";
    $res = $cmd->query($sql);
    $cuentas = $res->fetchAll();
} catch (Exception $e) {
    echo $e->getMessage();
}
$acum = [];
foreach ($datos as $dato) {
    $cuenta = $dato['cuenta'];
    foreach ($cuentas as $c) {
        if (($c['tipo_dato'] == 'M' && strpos($cuenta, $c['cuenta']) === 0) || ($c['tipo_dato'] != 'M' && $cuenta == $c['cuenta'])) {
            if (!isset($acum[$c['cuenta']])) {
                $acum[$c['cuenta']] = [
                    'cuenta' => $c['cuenta'],
                    'nombre' => $c['nombre'],
                    'debitoi' => 0,
                    'creditoi' => 0,
                    'debito' => 0,
                    'credito' => 0,
                    'tipo' => $c['tipo_dato']
                ];
            }
            $acum[$c['cuenta']]['debitoi'] += $dato['debitoi'];
            $acum[$c['cuenta']]['creditoi'] += $dato['creditoi'];
            $acum[$c['cuenta']]['debito'] += $dato['debito'];
            $acum[$c['cuenta']]['credito'] += $dato['credito'];
        }
    }
}

$nom_informe = "LIBRO MAYOR Y BALANCE";
include_once '../../financiero/encabezado_empresa.php';

?>
<table style="width:100% !important; border-collapse: collapse;" border="1">
    <thead>
        <tr>
            <td>FECHA INICIO</td>
            <td style='text-align: left;'><?php echo $fecha_inicial; ?></td>
            <td>FECHA FIN</td>
            <td style='text-align: left;'><?php echo $fecha_corte; ?></td>
        </tr>
    </thead>
</table>
<table class="table-hover" style="width:100% !important; border-collapse: collapse;" border="1">
    <thead>
        <tr class="centrar">
            <td>Cuenta</td>
            <td>Nombre</td>
            <td>Tipo</td>
            <td>Inicial</td>
            <td>Debito</td>
            <td>Credito</td>
            <td>Saldo Final</td>
        </tr>
    </thead>
    <tbody id="tbBalancePrueba">
        <?php
        if (!empty($acum)) {
            foreach ($acum as $tp) {
                $nat1 = substr($tp['cuenta'], 0, 1);
                $nat2 = substr($tp['cuenta'], 0, 2);
                if ($nat1 == '1' || $nat1 == '5' || $nat1 == '6' || $nat1 == '7' || $nat2 == '81' || $nat2 == '83' || $nat2 == '99') {
                    $naturaleza = "D";
                }
                if ($nat1 == '2' || $nat1 == '3' || $nat1 == '4' || $nat2 == '91' || $nat2 == '92'  || $nat2 == '93' || $nat2 == '89') {
                    $naturaleza = "C";
                }
                if ($naturaleza == "D") {
                    $saldo_ini = $tp['debitoi'] - $tp['creditoi'];
                    $saldo = $saldo_ini + $tp['debito'] - $tp['credito'];
                } else {
                    $saldo_ini = $tp['creditoi'] - $tp['debitoi'];
                    $saldo = $saldo_ini + $tp['credito'] - $tp['debito'];
                }

                echo "<tr>
                    <td class='text'>" . $tp['cuenta'] . "</td>
                    <td class='text'>" . $tp['nombre'] . "</td>
                    <td class='text-center'>" . $tp['tipo'] . "</td>
                    <td class='text-right'>" . $saldo_ini . "</td>
                    <td class='text-right'>" . $tp['debito'] . "</td>
                    <td class='text-right'>" . $tp['credito'] . "</td>
                    <td class='text-right'>" . $saldo . "</td>
                    </tr>";
                $saldo_ini = 0;
                $saldo = 0;
            }
        } else {
            echo "<tr><td colspan='7'>No hay datos para mostrar</td></tr>";
        }
        ?>
    </tbody>
</table>