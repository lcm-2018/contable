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
$where = '';
if ($_POST['xtercero'] == 1) {
    $where = ", `t1`.`id_tercero_api`";
}
// contar los caracteres de $cuenta_ini
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
try {
    $sql = "SELECT 
                `ctb_pgcp`.`cuenta`
                , `ctb_pgcp`.`nombre`
                , `ctb_pgcp`.`tipo_dato` AS `tipo`
                , `t1`.`id_tercero_api`
                , SUM(`t1`.`debitoi`) AS `debitoi`
                , SUM(`t1`.`creditoi`) AS `creditoi`
                , SUM(`t1`.`debito`) AS `debito`
                , SUM(`t1`.`credito`) AS `credito`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
            FROM
                (SELECT
                    `ctb_libaux`.`id_cuenta`
                    , `ctb_libaux`.`id_tercero_api`
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
                    AND ((SUBSTRING(`ctb_pgcp`.`cuenta`, 1, 1) IN ('1', '2', '3', '8', '9') AND DATE_FORMAT(`ctb_doc`.`fecha`, '%Y-%m-%d') < '$fecha_inicial')
                        OR
                    (SUBSTRING(`ctb_pgcp`.`cuenta`, 1, 1) IN ('4', '5', '6') AND DATE_FORMAT(`ctb_doc`.`fecha`, '%Y-%m-%d') < '$fecha_inicial' AND DATE_FORMAT(`ctb_doc`.`fecha`, '%Y-%m-%d') >= '$inicio'))
                GROUP BY `ctb_libaux`.`id_cuenta`, `ctb_libaux`.`id_tercero_api`
                UNION ALL 
                SELECT
                    `ctb_libaux`.`id_cuenta`
                    , `ctb_libaux`.`id_tercero_api`
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
                WHERE (DATE_FORMAT(`ctb_doc`.`fecha`, '%Y-%m-%d') BETWEEN '$fecha_inicial' AND '$fecha_corte' AND `ctb_doc`.`estado` = 2)
                GROUP BY `ctb_libaux`.`id_cuenta`, `ctb_libaux`.`id_tercero_api`) AS `t1`
                INNER JOIN `ctb_pgcp`
                    ON `t1`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`
                LEFT JOIN `tb_terceros`
                    ON (`t1`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`)
            GROUP BY `t1`.`id_cuenta` $where
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
        $idTer = $_POST['xtercero'] == 1 && $c['tipo_dato'] == 'D' ? '-' . $dato['id_tercero_api'] : '';
        if (!($_POST['xtercero'] == 1 && (substr($c['cuenta'], 0, 1) === "2" || substr($c['cuenta'], 0, 2) === "13" || substr($c['cuenta'], 0, 1) === "8" || substr($c['cuenta'], 0, 1) === "9"))) {
            $idTer = '';
        }
        if (($c['tipo_dato'] == 'M' && strpos($cuenta, $c['cuenta']) === 0) || ($c['tipo_dato'] != 'M' && $cuenta == $c['cuenta'])) {
            $cta = $c['cuenta'] . $idTer;
            if (!isset($acum[$c['cuenta']])) {
                $acum[$cta] = [
                    'cuenta' => $c['cuenta'],
                    'nombre' => $c['nombre'],
                    'debitoi' => 0,
                    'creditoi' => 0,
                    'debito' => 0,
                    'credito' => 0,
                    'tipo' => $c['tipo_dato']
                ];
            }
            $acum[$cta]['debitoi'] += $dato['debitoi'];
            $acum[$cta]['creditoi'] += $dato['creditoi'];
            $acum[$cta]['debito'] += $dato['debito'];
            $acum[$cta]['credito'] += $dato['credito'];
            if ($_POST['xtercero'] == 1 && (substr($c['cuenta'], 0, 1) === "2" || substr($c['cuenta'], 0, 2) === "13" || substr($c['cuenta'], 0, 1) === "8" || substr($c['cuenta'], 0, 1) === "9") && $c['tipo_dato'] == 'D') {
                $acum[$cta]['id_tercero_api'] = $dato['id_tercero_api'];
                $acum[$cta]['nom_tercero'] = $dato['nom_tercero'];
                $acum[$cta]['nit_tercero'] = $dato['nit_tercero'];
            }
        }
    }
}
$nom_informe = "LIBRO MAYOR Y BALANCE";
include_once '../../financiero/encabezado_empresa.php';

?>
<br>
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
            <th>Cuenta</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <?php if ($_POST['xtercero'] == 1) { ?>
                <th>Tercero</th>
                <th>Nit</th>
            <?php } ?>
            <th>Inicial</th>
            <th>Debito</th>
            <th>Credito</th>
            <th>Saldo Final</th>
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

                if ($_POST['xtercero'] == 1) {
                    $tercero = isset($tp['nom_tercero']) ? $tp['nom_tercero'] : '';
                    $nit = isset($tp['nit_tercero']) ? $tp['nit_tercero'] : '';
                    $dter = "<td class='text'>" . $tercero . "</td>
                    <td class='text'>" . $nit . "</td>";
                } else {
                    $dter = "";
                }
                echo "<tr>
                    <td class='text'>" . $tp['cuenta'] . "</td>
                    <td class='text'>" . mb_convert_encoding($tp['nombre'], 'UTF-8') . "</td>
                    <td class='text-center'>" . $tp['tipo'] . "</td>" . $dter . "
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