<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$vigencia = $_SESSION['vigencia'];
$fecha_corte = $_POST['fecha_corte'];
$detalle_mes = $_POST['mes'];
$fecha_ini = $_POST['fecha_ini'];
$mes = date("m", strtotime($fecha_corte));
$fecha_ini_mes = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-' . $mes . '-01'));
function pesos($valor)
{
    return number_format($valor, 2, ".", ",");
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

try {
    $sql = "SELECT 
                `pto_cargue`.`id_cargue`
                , `pto_cargue`.`id_pto`
                , `pto_cargue`.`cod_pptal`
                , `pto_cargue`.`nom_rubro`
                , `pto_cargue`.`tipo_dato`
                , `pto_cargue`.`valor_aprobado` AS `inicial`
                , IFNULL(`adicion`.`valor`,0) AS `val_adicion` 
                , IFNULL(`reduccion`.`valor`,0) AS `val_reduccion` 
                , IFNULL(`recaudo`.`valor`,0) AS `val_recaudo`
                , IFNULL(`reconocimiento`.`valor`,0) AS `val_reconocimiento`
                , IFNULL(`adicion_mes`.`valor`,0) AS `val_adicion_mes` 
                , IFNULL(`reduccion_mes`.`valor`,0) AS `val_reduccion_mes`
                , IFNULL(`recaudo_mes`.`valor`,0) AS `val_recaudo_mes`
                , IFNULL(`reconocimiento_mes`.`valor`,0) AS `val_reconocimiento_mes`
                , `pto_presupuestos`.`id_tipo`
            FROM `pto_cargue`
                INNER JOIN `pto_presupuestos`
                    ON (`pto_cargue`.`id_pto` = `pto_presupuestos`.`id_pto`)
                LEFT JOIN
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_deb`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                            ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                        INNER JOIN `pto_presupuestos` 
                            ON (`pto_mod`.`id_pto` = `pto_presupuestos`.`id_pto`)
                    WHERE (DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `adicion`
                    ON(`adicion`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_cred`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                            ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 3)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `reduccion`
                    ON(`reduccion`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        SUM(IFNULL(`ctt2`.`valor`,0) - IFNULL(`ctt2`.`valor_liberado`,0)) AS `valor`
                        ,`ctt2`.`id_rubro`
                    FROM
                        (SELECT
                            `pto_rec_detalle`.`valor`
                            , `pto_rec_detalle`.`valor_liberado`
                            , CASE
                                WHEN `pto_rec_detalle`.`id_rubro`IS NULL THEN `pto_rad_detalle`.`id_rubro`
                                ELSE `pto_rec_detalle`.`id_rubro` 
                            END AS `id_rubro`
                        FROM
                            `pto_rec_detalle`
                            INNER JOIN `pto_rec` 
                                ON (`pto_rec_detalle`.`id_pto_rac` = `pto_rec`.`id_pto_rec`)
                            LEFT JOIN `pto_rad_detalle` 
                                ON (`pto_rec_detalle`.`id_pto_rad_detalle` = `pto_rad_detalle`.`id_pto_rad_det`)
                        WHERE (`pto_rec`.`estado` = 2 AND DATE_FORMAT(`pto_rec`.`fecha`,'%Y-%m-%d') BETWEEN '$fecha_ini' AND '$fecha_corte')) AS `ctt2`
                    GROUP BY `ctt2`.`id_rubro`) AS `recaudo`
                    ON(`recaudo`.`id_rubro` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_rad_detalle`.`id_rubro`
                        , SUM(IFNULL(`pto_rad_detalle`.`valor`,0) - IFNULL(`pto_rad_detalle`.`valor_liberado`,0)) AS `valor`
                    FROM
                        `pto_rad_detalle`
                        INNER JOIN `pto_rad` 
                            ON (`pto_rad_detalle`.`id_pto_rad` = `pto_rad`.`id_pto_rad`)
                    WHERE (DATE_FORMAT(`pto_rad`.`fecha`,'%Y-%m-%d') BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_rad`.`estado` = 2)
                    GROUP BY `pto_rad_detalle`.`id_rubro`) AS `reconocimiento`
                    ON(`reconocimiento`.`id_rubro` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_deb`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                            ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                        INNER JOIN `pto_presupuestos` 
                            ON (`pto_mod`.`id_pto` = `pto_presupuestos`.`id_pto`)
                    WHERE (DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN '$fecha_ini_mes' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `adicion_mes`
                    ON(`adicion_mes`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_cred`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                            ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (DATE_FORMAT(`pto_mod`.`fecha`,'%Y-%m-%d') BETWEEN '$fecha_ini_mes' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 3)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `reduccion_mes`
                    ON(`reduccion_mes`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        SUM(IFNULL(`ctt2`.`valor`,0) - IFNULL(`ctt2`.`valor_liberado`,0)) AS `valor`
                        ,`ctt2`.`id_rubro`
                    FROM
                        (SELECT
                            `pto_rec_detalle`.`valor`
                            , `pto_rec_detalle`.`valor_liberado`
                            , CASE
                                WHEN `pto_rec_detalle`.`id_rubro`IS NULL THEN `pto_rad_detalle`.`id_rubro`
                                ELSE `pto_rec_detalle`.`id_rubro` 
                            END AS `id_rubro`
                        FROM
                            `pto_rec_detalle`
                            INNER JOIN `pto_rec` 
                                ON (`pto_rec_detalle`.`id_pto_rac` = `pto_rec`.`id_pto_rec`)
                            lEFT JOIN `pto_rad_detalle` 
                                ON (`pto_rec_detalle`.`id_pto_rad_detalle` = `pto_rad_detalle`.`id_pto_rad_det`)
                        WHERE (`pto_rec`.`estado` = 2 AND DATE_FORMAT(`pto_rec`.`fecha`,'%Y-%m-%d') BETWEEN '$fecha_ini_mes' AND '$fecha_corte')) AS `ctt2`
                    GROUP BY `ctt2`.`id_rubro`) AS `recaudo_mes`
                    ON(`recaudo_mes`.`id_rubro` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_rad_detalle`.`id_rubro`
                        , SUM(IFNULL(`pto_rad_detalle`.`valor`,0) - IFNULL(`pto_rad_detalle`.`valor_liberado`,0)) AS `valor`
                    FROM
                        `pto_rad_detalle`
                        INNER JOIN `pto_rad` 
                            ON (`pto_rad_detalle`.`id_pto_rad` = `pto_rad`.`id_pto_rad`)
                    WHERE (DATE_FORMAT(`pto_rad`.`fecha`,'%Y-%m-%d') BETWEEN '$fecha_ini_mes' AND '$fecha_corte' AND `pto_rad`.`estado` = 2)
                    GROUP BY `pto_rad_detalle`.`id_rubro`) AS `reconocimiento_mes`
                    ON(`reconocimiento_mes`.`id_rubro` = `pto_cargue`.`id_cargue`)
                WHERE (`pto_presupuestos`.`id_tipo` = 1)
                ORDER BY `pto_cargue`.`cod_pptal` ASC";
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$acum = [];
foreach ($rubros as $rb) {
    $rubro = $rb['cod_pptal'];

    $filtro = array_filter($rubros, function ($item) use ($rubro) {
        return strpos($item['cod_pptal'], $rubro) === 0;
    });

    if (!empty($filtro)) {
        $acum[$rubro] = [
            'inicial' => 0,
            'adicion' => 0,
            'reduccion' => 0,
            'reconocimiento' => 0,
            'recaudo' => 0,
            'adicion_mes' => 0,
            'reduccion_mes' => 0,
            'reconocimiento_mes' => 0,
            'recaudo_mes' => 0,
        ];

        // Procesar cada rubro filtrado
        foreach ($filtro as $f) {
            if ($f['tipo_dato'] == 1) {
                $acum[$rubro]['inicial'] += $f['inicial'];
                $acum[$rubro]['adicion'] += $f['val_adicion'];
                $acum[$rubro]['reduccion'] += $f['val_reduccion'];
                $acum[$rubro]['reconocimiento'] += $f['val_reconocimiento'];
                $acum[$rubro]['recaudo'] += $f['val_recaudo'];

                if ($detalle_mes == '1') {
                    $acum[$rubro]['adicion_mes'] += $f['val_adicion_mes'];
                    $acum[$rubro]['reduccion_mes'] += $f['val_reduccion_mes'];
                    $acum[$rubro]['reconocimiento_mes'] += $f['val_reconocimiento_mes'];
                    $acum[$rubro]['recaudo_mes'] += $f['val_recaudo_mes'];
                }
            }
        }
    }
}

try {
    $sql = "SELECT
                 `razon_social_ips`AS `nombre`, `nit_ips` AS `nit`, `dv` AS `dig_ver`
            FROM
                `tb_datos_ips`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<style>
    .resaltar:nth-child(even) {
        background-color: #F8F9F9;
    }

    .resaltar:nth-child(odd) {
        background-color: #ffffff;
    }
</style>
<table style="width:100% !important; border-collapse: collapse;" class="table-hover">
    <thead>
        <tr>
            <td rowspan="4" style="text-align:center"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
            <td colspan="13" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
        </tr>
        <tr>
            <td colspan="13" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
        </tr>
        <tr>
            <td colspan="13" style="text-align:center"><?php echo 'EJECUCION PRESUPUESTAL DE INGRESOS'; ?></td>
        </tr>
        <tr>
            <td colspan="13" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
        </tr>
        <tr style="background-color: #CED3D3; text-align:center;font-size:9px;">
            <td>Código</td>
            <td>Nombre</td>
            <td>Tipo</td>
            <td>Inicial</td>
            <?= $detalle_mes == '1' ? '<td>Adiciones mes</td>' : ''; ?>
            <td>adicion acumulada</td>
            <?= $detalle_mes == '1' ? '<td>Reducciones mes</td>' : ''; ?>
            <td>Reducción acumulada</td>
            <td>Definitivo</td>
            <?= $detalle_mes == '1' ? '<td>Reconocimiento mes</td>' : ''; ?>
            <td>Reconocimiento acumulado</td>
            <?= $detalle_mes == '1' ? '<td>Recaudo mes</td>' : ''; ?>
            <td>Recaudo acumulado</td>
            <td>% Ejec</td>
            <td>Saldo por recaudar</td>
        </tr>
    </thead>
    <tbody style="font-size:9px;">
        <?php
        foreach ($acum as $key => $value) {
            $definitivo = 0;
            $saldo_recaudar = 0;

            $keyrb = array_search($key, array_column($rubros, 'cod_pptal'));
            $nomrb = $keyrb !== false ? $rubros[$keyrb]['nom_rubro'] : '';
            $tipo = $keyrb !== false ? $rubros[$keyrb]['tipo_dato'] : '99';

            $tipo_dat = $tipo == '0' ? 'M' : 'D';
            $definitivo = $value['inicial'] + $value['adicion'] - $value['reduccion'];
            $saldo_recaudar = $definitivo - $value['recaudo'];
            $div = $value['inicial'] + $value['adicion'] - $value['reduccion'];
            $div = $div == 0 ? 1 : $div;
            echo '<tr>
                    <td class="text">' . $key . '</td>
                    <td class="text">' . $nomrb . '</td>
                    <td class="text">' . $tipo_dat . '</td>
                    <td style="text-align:right">' . pesos($value['inicial']) . '</td>';
            echo  $detalle_mes == '1' ? '<td style="text-align:right">' . pesos($value['adicion_mes']) . '</td>' : '';
            echo '<td style="text-align:right">' . pesos($value['adicion']) . '</td>';
            echo  $detalle_mes == '1' ? '<td style="text-align:right">' . pesos($value['reduccion_mes']) . '</td>' : '';
            echo '<td style="text-align:right">' . pesos($value['reduccion']) . '</td>';
            echo '<td style="text-align:right">' . pesos(($value['inicial'] + $value['adicion'] - $value['reduccion'])) . '</td>';
            echo  $detalle_mes == '1' ? '<td style="text-align:right">' . pesos($value['reconocimiento_mes']) . '</td>' : '';
            echo '<td style="text-align:right">' . pesos($value['reconocimiento']) . '</td>';
            echo  $detalle_mes == '1' ? '<td style="text-align:right">' . pesos($value['recaudo_mes']) . '</td>' : '';
            echo '<td style="text-align:right">' . pesos($value['recaudo']) . '</td>
             <td style="text-align:right">' .  round(($value['recaudo'] / $div) * 100, 2) . '</td>
                    <td style="text-align:right">' .  pesos($saldo_recaudar) . '</td>
                </tr>';
        }
        ?>
    </tbody>
</table>