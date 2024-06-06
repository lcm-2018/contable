<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$vigencia = $_SESSION['vigencia'];
$tipo_pto = isset($_POST['tipo_ppto']) ? $_POST['tipo_ppto'] : exit('Acceso no permitido');
$id_corte = $_POST['fecha_corte'];
$informe = $_POST['informe'];
$fecha_ini = $vigencia . '-01-01';
switch ($id_corte) {
    case 1:
        $fecha_corte = $vigencia . '-03-31';
        $codigo = '10303';
        break;
    case 2:
        $fecha_corte = $vigencia . '-06-30';
        $codigo = '10606';
        break;
    case 3:
        $fecha_corte = $vigencia . '-09-30';
        $codigo = '10909';
        break;
    case 4:
        $fecha_corte = $vigencia . '-03-31';
        $codigo = '11212';
        break;
    default:
        exit();
        break;
}
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT
                `razon_social_ips` AS `nombre`
                , `nit_ips` AS `nit`
                , `dv` AS `dig_ver`
            FROM
                `tb_datos_ips`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if ($informe == 1) {
    try {
        $sql = "SELECT 
                `pto_cargue`.`id_cargue`
                , `pto_cargue`.`id_pto`
                , `pto_cargue`.`cod_pptal`
                , `pto_cargue`.`nom_rubro`
                , `pto_cargue`.`tipo_dato`
                , `pto_codigo_cgr`.`codigo` AS `codigo_cgr`
                , `pto_codigo_cgr`.`id_cod` AS `id_cgr`
                , `pto_cargue`.`valor_aprobado` AS `inicial`
                , IFNULL(`adicion`.`valor`,0) AS `adicion` 
                , IFNULL(`reduccion`.`valor`,0) AS `reduccion` 
                , IFNULL(`credito`.`valor`,0) AS `credito` 
                , IFNULL(`contracredito`.`valor`,0) AS `contracredito` 
                , IFNULL(`comprometido`.`valor`,0) AS `val_comprometido` 
                , IFNULL(`registrado`.`valor`,0) AS `val_registrado` 
                , IFNULL(`causado`.`valor`,0) AS `val_causado` 
                , IFNULL(`pagado`.`valor`,0) AS `val_pagado`
            FROM `pto_cargue`
                INNER JOIN `pto_homologa_gastos` 
                    ON (`pto_homologa_gastos`.`id_cargue` = `pto_cargue`.`id_cargue`)
                INNER JOIN `pto_codigo_cgr` 
                    ON (`pto_homologa_gastos`.`id_cgr` = `pto_codigo_cgr`.`id_cod`)
                LEFT JOIN
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_deb`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                            ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 2)
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
                    WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 3)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `reduccion`
                    ON(`reduccion`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_deb`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                            ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 1)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `credito`
                    ON(`credito`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_deb`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                            ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 6)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `contracredito`
                    ON(`contracredito`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_cdp_detalle`.`id_rubro`
                        , SUM(IFNULL(`pto_cdp_detalle`.`valor`,0)) - SUM(IFNULL(`pto_cdp_detalle`.`valor_liberado`,0)) AS `valor`
                    FROM
                        `pto_cdp_detalle`
                        INNER JOIN `pto_cdp` 
                            ON (`pto_cdp_detalle`.`id_pto_cdp` = `pto_cdp`.`id_pto_cdp`)
                        INNER JOIN `pto_cargue` 
                            ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                    WHERE (`pto_cdp`.`estado` = 2 AND `pto_cdp`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte')
                    GROUP BY `pto_cdp_detalle`.`id_rubro`) AS `comprometido`
                    ON(`comprometido`.`id_rubro` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_cdp_detalle`.`id_rubro`
                        , SUM(IFNULL(`pto_crp_detalle`.`valor`,0)) - SUM(IFNULL(`pto_crp_detalle`.`valor_liberado`,0)) AS `valor`
                    FROM
                        `pto_crp_detalle`
                        INNER JOIN `pto_crp` 
                            ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                        INNER JOIN `pto_cdp_detalle` 
                            ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                    WHERE (`pto_crp`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_crp`.`estado` = 2)
                    GROUP BY `pto_cdp_detalle`.`id_rubro`) AS `registrado`
                    ON(`registrado`.`id_rubro` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_cdp_detalle`.`id_rubro`
                        , SUM(IFNULL(`pto_cop_detalle`.`valor`,0)) - SUM(IFNULL(`pto_cop_detalle`.`valor_liberado`,0)) AS `valor`
                    FROM
                        `pto_cop_detalle`
                        INNER JOIN `ctb_doc` 
                            ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        INNER JOIN `pto_crp_detalle` 
                            ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                        INNER JOIN `pto_cdp_detalle` 
                            ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                    WHERE (`ctb_doc`.`estado` = 2 AND `ctb_doc`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte')
                    GROUP BY `pto_cdp_detalle`.`id_rubro`) AS `causado`
                    ON(`causado`.`id_rubro` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_cdp_detalle`.`id_rubro`
                        , SUM(IFNULL(`pto_pag_detalle`.`valor`,0)) - SUM(IFNULL(`pto_pag_detalle`.`valor_liberado`,0)) AS `valor`
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
                    WHERE (`ctb_doc`.`estado` = 2 AND `ctb_doc`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte')
                    GROUP BY `pto_cdp_detalle`.`id_rubro`) AS `pagado`
                    ON(`pagado`.`id_rubro` = `pto_cargue`.`id_cargue`)";
        $res = $cmd->query($sql);
        $rubros = $res->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    $data = [];
    foreach ($rubros as $fila) {
        $id = $fila['id_cgr'];
        $ini = $fila['inicial'];
        $def = $fila['inicial'] + $fila['adicion'] - $fila['reduccion'] + $fila['credito'] - $fila['contracredito'];
        if (isset($data[$id])) {
            $val_i = $data[$fila['id_cgr']]['inicial'];
            $val_d = $data[$fila['id_cgr']]['definitivo'];
            $val_ini = $val_i + $ini;
            $val_def = $val_d + $def;
        } else {
            $val_ini = $ini;
            $val_def = $def;
        }
        $data[$fila['id_cgr']] = [
            'codigo' => $fila['codigo_cgr'],
            'inicial' => $val_ini,
            'definitivo' => $val_def,
        ];
    }
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
            <td colspan="11" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
        </tr>
        <tr>
            <td colspan="11" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
        </tr>
        <tr>
            <td colspan="11" style="text-align:center"><?php echo 'CUIPO - INGRESOS'; ?></td>
        </tr>
        <tr>
            <td colspan="11" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
        </tr>
        <tr style="background-color: #CED3D3; text-align:center;">
            <td colspan="3">-</td>
            <td colspan="3">Codigo CGR</td>
            <td colspan="3">Pto. Inicial</td>
            <td colspan="3">Pto. Definitivo</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="3" style="text-align:center">S</td>
            <td colspan="3" style="text-align:center">84300000</td>
            <td colspan="3" style="text-align:center"><?php echo $codigo; ?></td>
            <td colspan="3" style="text-align:center"><?php echo $vigencia; ?></td>
        </tr>
        <?php
        if (!empty($data)) {
            foreach ($data as $key => $d) {
                if ($key != '') {
                    echo '<tr class="resaltar">';
                    echo '<td colspan="3">D</td>';
                    echo '<td colspan="3">' . $d['codigo'] . '</td>';
                    echo '<td colspan="3" style="text-align:right">' . $d['inicial'] . '</td>';
                    echo '<td colspan="3" style="text-align:right">' . $d['definitivo'] . '</td>';
                    echo '</tr>';
                }
            }
        }
        ?>
    </tbody>
</table>