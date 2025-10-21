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
$corte = $_POST['periodo'] == '' ? date('Y-m-d') : $_POST['periodo'];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

try {
    $sql = "WITH
                `cop` AS 
                (SELECT 
                    `cop`.`id_tercero_api`
                    , `cop`.`id_cuenta`
                    , JSON_ARRAYAGG(JSON_OBJECT('fecha', `cop`.`fecha`, 'valor', `cop`.`valor`)) AS movimientos
                    , SUM(IFNULL(`cop`.`valor`,0)) AS `val_cop`
                FROM
                    (SELECT
                        `ctb_libaux`.`id_tercero_api`
                        , `ctb_libaux`.`id_cuenta`
                        , DATE_FORMAT(`ctb_doc`.`fecha`,'%Y-%m-%d') AS `fecha`
                        , SUM(IFNULL(`ctb_libaux`.`credito`,0)) AS `valor`
                    FROM
                        `ctb_libaux`
                        INNER JOIN `ctb_doc` ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        INNER JOIN `ctb_pgcp` ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                    WHERE (DATE_FORMAT(`ctb_doc`.`fecha`,'%Y-%m-%d') <= '$corte' AND `ctb_doc`.`estado` = 2 AND `ctb_libaux`.`id_tercero_api` IS NOT NULL AND `ctb_pgcp`.`cuenta` LIKE '2%')
                    GROUP BY DATE_FORMAT(`ctb_doc`.`fecha`,'%Y-%m-%d'), `ctb_libaux`.`id_tercero_api`, `ctb_libaux`.`id_cuenta`
                    ORDER BY `ctb_libaux`.`id_tercero_api`, `ctb_libaux`.`id_cuenta`) AS `cop`
                WHERE `cop`.`valor` > 0
                GROUP BY `cop`.`id_tercero_api`,`cop`.`id_cuenta`
                ORDER BY `cop`.`id_tercero_api`,`cop`.`id_cuenta`),
                `pag` AS 
                    (SELECT
                        `ctb_libaux`.`id_tercero_api`
                        , `ctb_libaux`.`id_cuenta`
                        , SUM(IFNULL(`ctb_libaux`.`debito`,0)) AS `valor`
                    FROM
                        `ctb_libaux`
                        INNER JOIN `ctb_doc` ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        INNER JOIN `ctb_pgcp` ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                    WHERE (DATE_FORMAT(`ctb_doc`.`fecha`,'%Y-%m-%d') <= '$corte' AND `ctb_doc`.`estado` = 2 AND `ctb_pgcp`.`cuenta` LIKE '2%')
                    GROUP BY `ctb_libaux`.`id_tercero_api`, `ctb_libaux`.`id_cuenta`)
            SELECT 
                `cop`.`id_tercero_api`
                , `cop`.`id_cuenta`
                , `ctb_pgcp`.`cuenta`
                    , `ctb_pgcp`.`nombre`
                , LEFT(`ttd`.`codigo_ne`,2) AS `codigo_ne`
                , `tb_terceros`.`nit_tercero`
                , IF(LEFT(`ttd`.`codigo_ne`,2) = 'NI'
                , calcularDV(`tb_terceros`.`nit_tercero`),0) AS dv
                , `tb_terceros`.`nom_tercero`
                , `cop`.`movimientos`
                , `pag`.`valor` AS `pagado`
            FROM `cop`
                INNER JOIN `ctb_pgcp` ON `cop`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`
                LEFT JOIN `tb_terceros` ON `cop`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`
                LEFT JOIN `tb_tipos_documento` `ttd` ON `tb_terceros`.`tipo_doc` = `ttd`.`id_tipodoc`
                LEFT JOIN `pag` ON (`cop`.`id_tercero_api` = `pag`.`id_tercero_api` AND `cop`.`id_cuenta` = `pag`.`id_cuenta`)
            WHERE `cop`.`val_cop` > `pag`.`valor`";
    $res = $cmd->query($sql);
    $detalles = $res->fetchAll(PDO::FETCH_ASSOC);
    $res->closeCursor();
    unset($res);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$fechaCorte = new DateTime($corte);

$id_t = [];

foreach ($detalles as $r) {
    $id_t[] = $r['id_tercero_api'];
}
$id_t = array_values(array_unique($id_t));
$payload = json_encode($id_t);
//API URL
$url = $api . 'terceros/datos/res/lista/reportes';
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$result = curl_exec($ch);
curl_close($ch);
$datos = json_decode($result, true) ?? [];
$datos = array_column($datos, 'actividades', 'id_tercero');

$body = '';
$total = 0;
try {
    foreach ($detalles as $r) {
        $actividad = $datos[$r['id_tercero_api']] ?? 'NA';
        $actividad = $actividad == '' ? 'NA' : implode(', ', array_unique(explode('|', $actividad)));
        $r['0-30'] = $r['31-60'] = $r['61-90'] = $r['91-180'] = $r['181-360'] = $r['>360'] = 0;
        $pagado = $pagos[$r['id_cuenta'] . '-' . $r['id_tercero_api']] ?? 0;

        $pendiente = 0;
        $pagado = $r['pagado'] ?? 0;
        $movimientos = null;
        $movimientos = json_decode($r['movimientos'], true) ?? [];
        $bandera = false;
        foreach ($movimientos as $mov) {
            $fecha = $mov['fecha'];
            $valor = $mov['valor'];
            if ($pagado >= $valor) {
                $pagado -= $valor;
                continue;
            } else {
                $valor -= $pagado;
                $pagado = 0;
                $fechaMov = new DateTime($fecha);
                $dias = $fechaCorte->diff($fechaMov)->days;
                if ($dias >= 0 && $dias <= 30) {
                    $r['0-30'] = $valor;
                } elseif ($dias >= 31 && $dias <= 60) {
                    $r['31-60'] += $valor;
                } elseif ($dias >= 61 && $dias <= 90) {
                    $r['61-90'] += $valor;
                } elseif ($dias >= 91 && $dias <= 180) {
                    $r['91-180'] += $valor;
                } elseif ($dias >= 181 && $dias <= 360) {
                    $r['181-360'] += $valor;
                } elseif ($dias > 360) {
                    $r['>360'] += $valor;
                }
                $pendiente += $valor;
                $bandera = true;
            }
        }
        if ($bandera) {
            $total += $pendiente;
            $body .= "<tr>
                <td>{$r['cuenta']}</td>
                <td>{$r['nombre']}</td>
                <td>3</td>
                <td>{$r['codigo_ne']}</td>
                <td>{$r['nit_tercero']}</td>
                <td>{$r['dv']}</td>
                <td>{$r['nom_tercero']}</td>
                <td>$actividad</td>
                <td>6</td>
                <td>1</td>
                <td>0</td>
                <td>{$r['0-30']}</td>
                <td>{$r['31-60']}</td>
                <td>{$r['61-90']}</td>
                <td>{$r['91-180']}</td>
                <td>{$r['181-360']}</td>
                <td>{$r['>360']}</td>
                <td>0</td>
                <td>{$pendiente}</td>
                <td>10</td>
                <td></td>
            </tr>";
        }
    }
} catch (Exception $e) {
    echo 'Error al procesar los datos.';
    exit();
}
$cols = 21;
echo "\xEF\xBB\xBF";
?>
<table class="table-bordered bg-light" style="width:100% !important; font-size: 12px;" border=0>
    <tr>
        <td colspan="<?= $cols; ?>" style="text-align: center; font-weight: bold;">FT004 - CUENTAS POR PAGAR</td>
    </tr>
    <tr>
        <td colspan="<?= $cols; ?>" style="text-align: center; font-weight: bold;">FECHA CORTE: <?= $corte; ?></td>
    </tr>
    <tr class="text-center">
        <th>Cuenta</th>
        <th>Nombre</th>
        <th>LineaNegocio</th>
        <th>TipoIdAcreedor</th>
        <th>IdAcreedor</th>
        <th>dv</th>
        <th>DvAcreedor</th>
        <th>ActividadAcreedor</th>
        <th>ConceptoAcreencia</th>
        <th>MedicionPosterior</th>
        <th>CxPNoVencidas</th>
        <th>CxPMora 30 días</th>
        <th>CxPMora 31 a 60 días</th>
        <th>CxPMora 61 a 90 días</th>
        <th>CxPMora 91 a 180 días</th>
        <th>CxPMora 181 a 360 días</th>
        <th>CxPMora > 360 días</th>
        <th>Ajuste</th>
        <th>Saldo</th>
        <th>CxP financiados con recursos</th>
        <th>Modalidad de pago</th>
    </tr>
    <tbody>
        <?= $body; ?>
        <tr>
            <td colspan="18" style="text-align: right; font-weight: bold;">TOTAL</td>
            <td style="font-weight: bold;"><?= number_format($total, 2, '.', ''); ?></td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>