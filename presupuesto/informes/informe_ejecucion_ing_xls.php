<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$vigencia = $_SESSION['vigencia'];
$fecha_corte = $_POST['fecha_corte'];
$detalle_mes = $_POST['mes'];
$fecha_ini = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-01-01'));
$mes = date("m", strtotime($fecha_corte));
$fecha_ini_mes = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-' . $mes . '-01'));
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
//
$valores_mes = '';
$join_mes = '';
if ($detalle_mes == 1) {
    $valores_mes = ", IFNULL(`adicion_mes`.`valor`,0) AS `val_adicion_mes` 
                , IFNULL(`reduccion_mes`.`valor`,0) AS `val_reduccion_mes`
                , IFNULL(`recaudo_mes`.`valor`,0) AS `val_reconocimiento_mes`
                , IFNULL(`reconocimiento_mes`.`valor`,0) AS `val_recaudo_mes`";
    $join_mes = "LEFT JOIN
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_deb`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                            ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                        INNER JOIN `pto_presupuestos` 
                            ON (`pto_mod`.`id_pto` = `pto_presupuestos`.`id_pto`)
                    WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini_mes' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 2 AND `pto_presupuestos`.`id_tipo` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `adicion_mes`
                    ON(`adicion_mes`.`id_cargue` = `pto_cargue`.`id_cargue`)
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
                    WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini_mes' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 3 AND `pto_presupuestos`.`id_tipo` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `reduccion_mes`
                    ON(`reduccion_mes`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_rad_detalle`.`id_rubro`
                        , SUM(IFNULL(`pto_rec_detalle`.`valor`,0)) - SUM(IFNULL(`pto_rec_detalle`.`valor_liberado`,0)) AS `valor`
                    FROM
                        `pto_rec_detalle`
                        INNER JOIN `pto_rad_detalle` 
                            ON (`pto_rec_detalle`.`id_pto_rad_detalle` = `pto_rad_detalle`.`id_pto_rad_det`)
                        INNER JOIN `pto_rec` 
                            ON (`pto_rec_detalle`.`id_pto_rac` = `pto_rec`.`id_pto_rec`)
                    WHERE (`pto_rec`.`fecha` BETWEEN '$fecha_ini_mes' AND '$fecha_corte' AND `pto_rec`.`estado` = 2)
                    GROUP BY `pto_rad_detalle`.`id_rubro`) AS `recaudo_mes`
                    ON(`recaudo_mes`.`id_rubro` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_rad_detalle`.`id_rubro`
                        , `pto_rad_detalle`.`valor`
                        , `pto_rad_detalle`.`valor_liberado`
                    FROM
                        `pto_rad_detalle`
                        INNER JOIN `pto_rad` 
                            ON (`pto_rad_detalle`.`id_pto_rad` = `pto_rad`.`id_pto_rad`)
                    WHERE (`pto_rad`.`fecha` BETWEEN '$fecha_ini_mes' AND '$fecha_corte' AND `pto_rad`.`estado` = 2)
                    GROUP BY `pto_rad_detalle`.`id_rubro`) AS `reconocimiento_mes`
                    ON(`reconocimiento_mes`.`id_rubro` = `pto_cargue`.`id_cargue`)";
}
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
                $valores_mes
            FROM `pto_cargue`
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
                    WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 2 AND `pto_presupuestos`.`id_tipo` = 1)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `adicion`
                    ON(`adicion`.`id_cargue` = `pto_cargue`.`id_cargue`)
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
                    WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 3 AND `pto_presupuestos`.`id_tipo` = 1)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `reduccion`
                    ON(`reduccion`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_rad_detalle`.`id_rubro`
                        , SUM(IFNULL(`pto_rec_detalle`.`valor`,0)) - SUM(IFNULL(`pto_rec_detalle`.`valor_liberado`,0)) AS `valor`
                    FROM
                        `pto_rec_detalle`
                        INNER JOIN `pto_rad_detalle` 
                            ON (`pto_rec_detalle`.`id_pto_rad_detalle` = `pto_rad_detalle`.`id_pto_rad_det`)
                        INNER JOIN `pto_rec` 
                            ON (`pto_rec_detalle`.`id_pto_rac` = `pto_rec`.`id_pto_rec`)
                    WHERE (`pto_rec`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_rec`.`estado` = 2)
                    GROUP BY `pto_rad_detalle`.`id_rubro`) AS `recaudo`
                    ON(`recaudo`.`id_rubro` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_rad_detalle`.`id_rubro`
                        , `pto_rad_detalle`.`valor`
                        , `pto_rad_detalle`.`valor_liberado`
                    FROM
                        `pto_rad_detalle`
                        INNER JOIN `pto_rad` 
                            ON (`pto_rad_detalle`.`id_pto_rad` = `pto_rad`.`id_pto_rad`)
                    WHERE (`pto_rad`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_rad`.`estado` = 2)
                    GROUP BY `pto_rad_detalle`.`id_rubro`) AS `reconocimiento`
                    ON(`reconocimiento`.`id_rubro` = `pto_cargue`.`id_cargue`)
                    $join_mes";
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$acum = [];
foreach ($rubros as $rb) {
    $rubro = $rb['cod_pptal'];
    $acum[$rubro] = $rb['cod_pptal'];
    $filtro = [];
    $filtro = array_filter($rubros, function ($rubros) use ($rubro) {
        return (strpos($rubros['cod_pptal'], $rubro) === 0);
    });
    if (!empty($filtro)) {
        foreach ($filtro as $f) {
            $val_inicial = $f['inicial'];
            $val_adicion = $f['val_adicion'];
            $val_reduccion = $f['val_reduccion'];
            $val_reconocimiento = $f['val_reconocimiento'];
            $val_recaudo = $f['val_recaudo'];
            $val_ini = isset($acum[$rubro]['inicial']) ? $acum[$rubro]['inicial'] : 0;
            $val_ad = isset($acum[$rubro]['adicion']) ? $acum[$rubro]['adicion'] : 0;
            $val_red = isset($acum[$rubro]['reduccion']) ? $acum[$rubro]['reduccion'] : 0;
            $val_reco = isset($acum[$rubro]['reconocimiento']) ? $acum[$rubro]['reconocimiento'] : 0;
            $val_reca = isset($acum[$rubro]['recaudo']) ? $acum[$rubro]['recaudo'] : 0;
            $acum[$rubro] = [
                'inicial' => $val_ini + $val_inicial,
                'adicion' => $val_adicion + $val_ad,
                'reduccion' => $val_adicion + $val_ad,
                'reconocimiento' => $val_reconocimiento + $val_reco,
                'recaudo' => $val_recaudo + $val_reca,
            ];
            if ($detalle_mes == 1) {
                $val_adicion_mes = $f['val_adicion_mes'];
                $val_reduccion_mes = $f['val_reduccion_mes'];
                $val_reconocimiento_mes = $f['val_reconocimiento_mes'];
                $val_recaudo_mes = $f['val_recaudo_mes'];
                $val_ad_mes = isset($acum[$rubro]['adicion_mes']) ? $acum[$rubro]['adicion_mes'] : 0;
                $val_red_mes = isset($acum[$rubro]['reduccion_mes']) ? $acum[$rubro]['reduccion_mes'] : 0;
                $val_rec_mes = isset($acum[$rubro]['reconocimiento_mes']) ? $acum[$rubro]['reconocimiento_mes'] : 0;
                $val_reca_mes = isset($acum[$rubro]['recaudo_mes']) ? $acum[$rubro]['recaudo_mes'] : 0;
                $acum[$rubro] += [
                    'adicion_mes' => $val_adicion_mes + $val_ad_mes,
                    'reduccion_mes' => $val_reduccion_mes + $val_red_mes,
                    'reconocimiento_mes' => $val_reconocimiento_mes + $val_rec_mes,
                    'recaudo_mes' => $val_recaudo_mes + $val_reca_mes,
                ];
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
<table style="width:100% !important; border-collapse: collapse;">
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
            <td>C&oacute;digo</td>
            <td>Nombre</td>
            <td>Tipo</td>
            <td>Inicial</td>
            <?php if ($detalle_mes == 1) { ?>
                <td>Adiciones mes</td>
            <?php } ?>
            <td>adicion acumulada</td>
            <?php if ($detalle_mes == 1) { ?>
                <td>Reducción mes</td>
            <?php } ?>
            <td>Reducción acumulada</td>
            <td>Definitivo</td>
            <?php if ($detalle_mes == 1) { ?>
                <td>Reconocimiento mes</td>
            <?php } ?>
            <td>Reconocimiento acumulado</td>
            <?php if ($detalle_mes == 1) { ?>
                <td>Recaudo mes</td>
            <?php } ?>
            <td>Recaudo acumulado</td>
            <td>Saldo por recaudar</td>
        </tr>
    </thead>
    <tbody style="font-size:9px;">
        <?php
        foreach ($acum as $key => $value) {
            $definitivo = 0;
            $saldo_recaudar = 0;
            $keyrb = array_search($key, array_column($rubros, 'cod_pptal'));
            if ($keyrb !== false) {
                $nomrb = $rubros[$keyrb]['nom_rubro'];
                $tipo = $rubros[$keyrb]['tipo_dato'];
            } else {
                $nomrb = '';
            }
            if ($tipo == '0') {
                $tipo_dat = 'M';
            } else {
                $tipo_dat = 'D';
            }
            $definitivo = $value['inicial'] + $value['adicion'] - $value['reduccion'];
            $saldo_recaudar = $definitivo - $value['recaudo'];
            echo '<tr>';
            echo '<td class="text">' . $key . '</td>';
            echo '<td class="text">' . $nomrb . '</td>';
            echo '<td class="text">' . $tipo_dat . '</td>';
            echo '<td style="text-align:right">' . number_format($value['inicial'], 2, ".", ",") . '</td>';
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . number_format($value['adicion_mes'], 2, ".", ",") . '</td>';
            }
            echo '<td style="text-align:right">' . number_format($value['adicion'], 2, ".", ",") . '</td>';
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . number_format($value['reduccion_mes'], 2, ".", ",") . '</td>';
            }
            echo '<td style="text-align:right">' . number_format($value['reduccion'], 2, ".", ",") . '</td>';
            echo '<td style="text-align:right">' . number_format(($value['inicial'] + $value['adicion'] - $value['reduccion']), 2, ".", ",") . '</td>';
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . number_format($value['reconocimiento_mes'], 2, ".", ",") . '</td>';
            }
            echo '<td style="text-align:right">' . number_format($value['reconocimiento'], 2, ".", ",") . '</td>';
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . number_format($value['recaudo_mes'], 2, ".", ",") . '</td>';
            }
            echo '<td style="text-align:right">' . number_format($value['recaudo'], 2, ".", ",") . '</td>';
            echo '<td style="text-align:right">' .  number_format($saldo_recaudar, 2, ".", ",") . '</td>';
            echo '</tr>';
        }
        ?>
    </tbody>
</table>