<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$vigencia = $_SESSION['vigencia'];
$tipo_pto = isset($_POST['tipo_ppto']) ? $_POST['tipo_ppto'] : exit('Acceso no permitido');
$id_corte = $_POST['fecha_corte'];
$informe = $_POST['informe'];
$fecha_ini = $vigencia . '-01-01';
$campos = '';
$join = '';
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
if ($tipo_pto == 1 && $informe == 1) {
    $titulo = "A_PROGRAMACION_DE _INGRESOS";
    $campos = ", IFNULL(`adicion`.`valor`,0) AS `adicion` 
                , IFNULL(`reduccion`.`valor`,0) AS `reduccion`
                ,'0' AS `credito`
                ,'0' AS `contracredito`";
    $join = "LEFT JOIN
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
                ON(`reduccion`.`id_cargue` = `pto_cargue`.`id_cargue`)";
} else if ($tipo_pto == 1 && $informe == 2) {
    $titulo = "B_EJECUCION_DE_INGRESOS";
    $campos = ", IFNULL(`recaudo`.`valor`,0) AS `recaudo`
                ,`pto_cpc`.`codigo` AS `codigo_cpc`
                , `pto_homologa_ingresos`.`id_fuente`
                , `pto_fuente`.`codigo` AS `codigo_fte`
                , `pto_homologa_ingresos`.`id_tercero`
                , `pto_terceros`.`codigo` AS `codigo_terc`
                , `pto_homologa_ingresos`.`id_politica`
                , `pto_politica`.`codigo` AS `codigo_pol`
                , `pto_homologa_ingresos`.`id_vigencia`
                , `pto_homologa_ingresos`.`id_situacion`";
    $join = "INNER JOIN `pto_cpc` 
                ON (`pto_homologa_ingresos`.`id_cpc` = `pto_cpc`.`id_cpc`)
            INNER JOIN `pto_fuente` 
                ON (`pto_homologa_ingresos`.`id_fuente` = `pto_fuente`.`id_fuente`)
            INNER JOIN `pto_terceros` 
                ON (`pto_homologa_ingresos`.`id_tercero` = `pto_terceros`.`id_tercero`)
            INNER JOIN `pto_politica` 
                ON (`pto_homologa_ingresos`.`id_politica` = `pto_politica`.`id_politica`)
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
                ON(`recaudo`.`id_rubro` = `pto_cargue`.`id_cargue`)";
} else if ($tipo_pto == 2 && $informe == 1) {
    $titulo = "C_PROGRAMACION_DE_GASTOS";
    $campos = ", IFNULL(`adicion`.`valor`,0) AS `adicion` 
                , IFNULL(`reduccion`.`valor`,0) AS `reduccion` 
                , IFNULL(`credito`.`valor`,0) AS `credito` 
                , IFNULL(`contracredito`.`valor`,0) AS `contracredito`";
    $join = "LEFT JOIN
                (SELECT
                    `pto_mod_detalle`.`id_cargue`
                    , SUM(`pto_mod_detalle`.`valor_deb`) AS `valor`
                FROM
                    `pto_mod_detalle`
                    INNER JOIN `pto_mod` 
                        ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    INNER JOIN `pto_presupuestos` 
                        ON (`pto_mod`.`id_pto` = `pto_presupuestos`.`id_pto`)
                WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 2 AND `pto_presupuestos`.`id_tipo` = 2)
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
                WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 3 AND `pto_presupuestos`.`id_tipo` = 2)
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
                ON(`contracredito`.`id_cargue` = `pto_cargue`.`id_cargue`)";
} else if ($tipo_pto == 2 && $informe == 2) {
    $titulo = "D_EJECUCION_DE_GASTOS";
    $campos = ", IFNULL(`registrado`.`valor`,0) AS `registrado` 
                , IFNULL(`causado`.`valor`,0) AS `causado` 
                , IFNULL(`pagado`.`valor`,0) AS `pagado`
                ,`pto_homologa_gastos`.`id_sector`
                , `pto_homologa_gastos`.`id_cpc`
                , `pto_cpc`.`codigo` AS `codigo_cpc`
                , `pto_homologa_gastos`.`id_fuente`
                , `pto_fuente`.`codigo` AS `codigo_fte`
                , `pto_homologa_gastos`.`id_situacion`
                , `pto_homologa_gastos`.`id_politica`
                , `pto_politica`.`codigo` AS `codigo_pol`
                , `pto_homologa_gastos`.`id_tercero`
                , `pto_terceros`.`codigo` AS `codigo_terc`
                , `pto_homologa_gastos`.`id_vigencia`
                , `pto_homologa_gastos`.`id_seccion`";
    $join = "INNER JOIN `pto_cpc` 
                ON (`pto_homologa_gastos`.`id_cpc` = `pto_cpc`.`id_cpc`)
            INNER JOIN `pto_fuente` 
                ON (`pto_homologa_gastos`.`id_fuente` = `pto_fuente`.`id_fuente`)
            INNER JOIN `pto_politica` 
                ON (`pto_homologa_gastos`.`id_politica` = `pto_politica`.`id_politica`)
            INNER JOIN `pto_terceros` 
                ON (`pto_homologa_gastos`.`id_tercero` = `pto_terceros`.`id_tercero`)
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
} else {
    exit('Algo salió mal');
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
$tabla = $tipo_pto == 1 ? 'pto_homologa_ingresos' : 'pto_homologa_gastos';
try {
    $sql = "SELECT 
                `pto_cargue`.`id_cargue`
                , `pto_codigo_cgr`.`codigo` AS `codigo_cgr`
                , `pto_codigo_cgr`.`id_cod` AS `id_cgr`
                , `pto_cargue`.`valor_aprobado` AS `inicial`
                $campos
            FROM `pto_cargue`
                INNER JOIN $tabla 
                    ON ($tabla.`id_cargue` = `pto_cargue`.`id_cargue`)
                INNER JOIN `pto_codigo_cgr` 
                    ON ($tabla.`id_cgr` = `pto_codigo_cgr`.`id_cod`)
                $join";
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$data = [];
if (($tipo_pto == 2 && $informe == 1) || ($tipo_pto == 1 && $informe == 1)) {
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
if ($tipo_pto == 1 && $informe == 2) {
    foreach ($rubros as $fila) {
        $id = $fila['id_cgr'] . '|' . $fila['codigo_cpc'] . '|' . $fila['codigo_fte'] . '|' . $fila['codigo_terc'] . '|' . $fila['codigo_pol'];
        $recaudo = $fila['recaudo'];
        if (isset($data[$id])) {
            $val_12 = $data[$id]['unodos'];
            $val_11 = $data[$id]['unouno'];
            $val_22 = $data[$id]['dosdos'];
            $val_21 = $data[$id]['dosuno'];
        } else {
            $val_12 = 0;
            $val_11 = 0;
            $val_22 = 0;
            $val_21 = 0;
        }
        $v_12 = 0;
        $v_11 = 0;
        $v_22 = 0;
        $v_21 = 0;
        switch ($fila['id_vigencia'] . $fila['id_situacion']) {
            case '12':
                $v_12 = $recaudo;
                break;
            case '11':
                $v_11 = $recaudo;
                break;
            case '22':
                $v_22 = $recaudo;
                break;
            case '21':
                $v_21 = $recaudo;
                break;
        }
        $data[$id] = [
            'codigo' => $fila['codigo_cgr'],
            'unodos' => $val_12 + $v_12,
            'unouno' => $val_11 + $v_11,
            'dosdos' => $val_22 + $v_22,
            'dosuno' => $val_21 + $v_21,
        ];
    }
}
if ($tipo_pto == 2 && $informe == 2) {
    foreach ($rubros as $fila) {
        $id = $fila['id_cgr'] . '|' . $fila['id_vigencia'] . '|' . $fila['id_seccion'] . '|' . $fila['id_sector'] . '|' . $fila['codigo_cpc'] . '|' . $fila['codigo_fte'] . '|' . $fila['id_situacion'] . '|' . $fila['codigo_pol'] . '|' . $fila['codigo_terc'];
        $registrado = $fila['registrado'];
        $causado = $fila['causado'];
        $pagado = $fila['pagado'];

        if (isset($data[$id])) {
            $val_r = $data[$id]['registrado'];
            $val_c = $data[$id]['causado'];
            $val_p = $data[$id]['pagado'];
            $val_reg = $val_r + $registrado;
            $val_cau = $val_c + $causado;
            $val_pag = $val_p + $pagado;
        } else {
            $val_reg = $registrado;
            $val_cau = $causado;
            $val_pag = $pagado;
        }
        $data[$id] = [
            'codigo' => $fila['codigo_cgr'],
            'registrado' => $val_reg,
            'causado' => $val_cau,
            'pagado' => $val_pag,
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
            <td colspan="13" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
        </tr>
        <tr>
            <td colspan="13" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
        </tr>
        <tr>
            <td colspan="13" style="text-align:center"><?php echo $tipo_pto == 1 ? 'CUIPO - INGRESOS' : 'CUIPO - GASTOS'; ?></td>
        </tr>
        <tr>
            <td colspan="13" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
        </tr>
        <tr style="background-color: #CED3D3; text-align:center;">
            <?php
            if (($tipo_pto == 2 && $informe == 1) || ($tipo_pto == 1 && $informe == 1)) {
            ?>
                <td colspan="3">-</td>
                <td colspan="3">Codigo CGR</td>
                <td colspan="4">Pto. Inicial</td>
                <td colspan="4">Pto. Definitivo</td>
            <?php
            }
            if ($tipo_pto == 1 && $informe == 2) {
            ?>
                <td>-</td>
                <td colspan="3">Codigo CGR</td>
                <td>CPC</td>
                <td>Fuente</td>
                <td>Tercero</td>
                <td>Política</td>
                <td>V_AC_S_S</td>
                <td>V_AC_C_S</td>
                <td>V_AN_S_S</td>
                <td>V_AN_C_S</td>
                <td>Total</td>
            <?php
            }
            if ($tipo_pto == 2 && $informe == 2) {
            ?>
                <td>-</td>
                <td>Codigo CGR</td>
                <td>Vigencia</td>
                <td>Seccion</td>
                <td>Sector</td>
                <td>CPC</td>
                <td>Fuente</td>
                <td>Situación</td>
                <td>Política</td>
                <td>Tercero</td>
                <td>Compromisos</td>
                <td>Obligaciones</td>
                <td>Pagos</td>
            <?php
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2" style="text-align:center">S</td>
            <td colspan="3" style="text-align:center">84300000</td>
            <td colspan="3" style="text-align:center"><?php echo $codigo; ?></td>
            <td colspan="3" style="text-align:center"><?php echo $vigencia; ?></td>
            <td colspan="3" style="text-align:center"><?php echo $titulo; ?></td>
        </tr>
        <?php
        if (!empty($data)) {

            foreach ($data as $key => $d) {
                if ($key != '') {
                    if (($tipo_pto == 2 && $informe == 1) || ($tipo_pto == 1 && $informe == 1)) {
                        echo '<tr class="resaltar">';
                        echo '<td colspan="3">D</td>';
                        echo '<td colspan="3">' . $d['codigo'] . '</td>';
                        echo '<td colspan="4" style="text-align:right">' . $d['inicial'] . '</td>';
                        echo '<td colspan="4" style="text-align:right">' . $d['definitivo'] . '</td>';
                        echo '</tr>';
                    }
                    if ($tipo_pto == 1 && $informe == 2) {
                        $id = explode('|', $key);
                        echo '<tr class="resaltar">';
                        echo '<td>D</td>';
                        echo '<td colspan="3">' . $d['codigo'] . '</td>';
                        echo '<td>' . $id[1] . '</td>';
                        echo '<td>' . $id[2] . '</td>';
                        echo '<td>' . $id[3] . '</td>';
                        echo '<td>' . $id[4] . '</td>';
                        echo '<td>' . ($d['unodos']) . '</td>';
                        echo '<td>' . ($d['unouno']) . '</td>';
                        echo '<td>' . ($d['dosdos']) . '</td>';
                        echo '<td>' . ($d['dosuno']) . '</td>';
                        echo '<td>' . ($d['unodos'] + $d['unouno'] + $d['dosdos'] + $d['dosuno']) . '</td>';
                        echo '</tr>';
                    }
                    if ($tipo_pto == 2 && $informe == 2) {
                        $id = explode('|', $key);
                        echo '<tr class="resaltar">';
                        echo '<td>D</td>';
                        echo '<td>' . $d['codigo'] . '</td>';
                        echo '<td>' . $id[1] . '</td>';
                        echo '<td>' . $id[2] . '</td>';
                        echo '<td>' . $id[3] . '</td>';
                        echo '<td>' . $id[4] . '</td>';
                        echo '<td>' . $id[5] . '</td>';
                        echo '<td>' . $id[6] . '</td>';
                        echo '<td>' . $id[7] . '</td>';
                        echo '<td>' . $id[8] . '</td>';
                        echo '<td>' . ($d['registrado']) . '</td>';
                        echo '<td>' . ($d['causado']) . '</td>';
                        echo '<td>' . ($d['pagado']) . '</td>';
                        echo '</tr>';
                    }
                }
            }
        }
        ?>
    </tbody>
</table>