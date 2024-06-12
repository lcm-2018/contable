<?php
session_start();
set_time_limit(5600);
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
                , IFNULL(`credito_mes`.`valor`,0) AS `val_credito_mes` 
                , IFNULL(`contracredito_mes`.`valor`,0) AS `val_contracredito_mes` 
                , IFNULL(`comprometido_mes`.`valor`,0) AS `val_comprometido_mes` 
                , IFNULL(`registrado_mes`.`valor`,0) AS `val_registrado_mes` 
                , IFNULL(`causado_mes`.`valor`,0) AS `val_causado_mes` 
                , IFNULL(`pagado_mes`.`valor`,0) AS `val_pagado_mes`";
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
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_deb`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                            ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                        INNER JOIN `pto_presupuestos` 
                            ON (`pto_mod`.`id_pto` = `pto_presupuestos`.`id_pto`)
                    WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini_mes' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 1 AND `pto_presupuestos`.`id_tipo` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `credito_mes`
                    ON(`credito_mes`.`id_cargue` = `pto_cargue`.`id_cargue`)
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
                    WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini_mes' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 6 AND `pto_presupuestos`.`id_tipo` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `contracredito_mes`
                    ON(`contracredito_mes`.`id_cargue` = `pto_cargue`.`id_cargue`)
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
                    WHERE (`pto_cdp`.`estado` = 2 AND `pto_cdp`.`fecha` BETWEEN '$fecha_ini_mes' AND '$fecha_corte')
                    GROUP BY `pto_cdp_detalle`.`id_rubro`) AS `comprometido_mes`
                    ON(`comprometido_mes`.`id_rubro` = `pto_cargue`.`id_cargue`)
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
                    WHERE (`pto_crp`.`fecha` BETWEEN '$fecha_ini_mes' AND '$fecha_corte' AND `pto_crp`.`estado` = 2)
                    GROUP BY `pto_cdp_detalle`.`id_rubro`) AS `registrado_mes`
                    ON(`registrado_mes`.`id_rubro` = `pto_cargue`.`id_cargue`)
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
                    WHERE (`ctb_doc`.`estado` = 2 AND `ctb_doc`.`fecha` BETWEEN '$fecha_ini_mes' AND '$fecha_corte')
                    GROUP BY `pto_cdp_detalle`.`id_rubro`) AS `causado_mes`
                    ON(`causado_mes`.`id_rubro` = `pto_cargue`.`id_cargue`)
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
                    WHERE (`ctb_doc`.`estado` = 2 AND `ctb_doc`.`fecha` BETWEEN '$fecha_ini_mes' AND '$fecha_corte')
                    GROUP BY `pto_cdp_detalle`.`id_rubro`) AS `pagado_mes`
                    ON(`pagado_mes`.`id_rubro` = `pto_cargue`.`id_cargue`)";
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
                , IFNULL(`credito`.`valor`,0) AS `val_credito` 
                , IFNULL(`contracredito`.`valor`,0) AS `val_contracredito` 
                , IFNULL(`comprometido`.`valor`,0) AS `val_comprometido` 
                , IFNULL(`registrado`.`valor`,0) AS `val_registrado` 
                , IFNULL(`causado`.`valor`,0) AS `val_causado` 
                , IFNULL(`pagado`.`valor`,0) AS `val_pagado`
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
                        INNER JOIN `pto_presupuestos` 
                            ON (`pto_mod`.`id_pto` = `pto_presupuestos`.`id_pto`)
                    WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 1 AND `pto_presupuestos`.`id_tipo` = 2)
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
                        INNER JOIN `pto_presupuestos` 
                            ON (`pto_mod`.`id_pto` = `pto_presupuestos`.`id_pto`)
                    WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 6 AND `pto_presupuestos`.`id_tipo` = 2)
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
                    ON(`pagado`.`id_rubro` = `pto_cargue`.`id_cargue`)
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
            $val_credito = $f['val_credito'];
            $val_contracredito = $f['val_contracredito'];
            $val_comprometido = $f['val_comprometido'];
            $val_registrado = $f['val_registrado'];
            $val_causado = $f['val_causado'];
            $val_pagado = $f['val_pagado'];
            $val_ini = isset($acum[$rubro]['inicial']) ? $acum[$rubro]['inicial'] : 0;
            $val_ad = isset($acum[$rubro]['adicion']) ? $acum[$rubro]['adicion'] : 0;
            $val_red = isset($acum[$rubro]['reduccion']) ? $acum[$rubro]['reduccion'] : 0;
            $val_cre = isset($acum[$rubro]['credito']) ? $acum[$rubro]['credito'] : 0;
            $val_ccre = isset($acum[$rubro]['contracredito']) ? $acum[$rubro]['contracredito'] : 0;
            $val_cdp = isset($acum[$rubro]['comprometido']) ? $acum[$rubro]['comprometido'] : 0;
            $val_crp = isset($acum[$rubro]['regitrado']) ? $acum[$rubro]['regitrado'] : 0;
            $val_cop = isset($acum[$rubro]['causado']) ? $acum[$rubro]['causado'] : 0;
            $val_pag = isset($acum[$rubro]['pagado']) ? $acum[$rubro]['pagado'] : 0;
            $acum[$rubro] = [
                'inicial' => $val_ini + $val_inicial,
                'adicion' => $val_adicion + $val_ad,
                'reduccion' => $val_adicion + $val_ad,
                'credito' => $val_reduccion + $val_red,
                'contracredito' => $val_contracredito + $val_ccre,
                'comprometido' => $val_comprometido + $val_cdp,
                'regitrado' => $val_registrado + $val_crp,
                'causado' => $val_causado + $val_cop,
                'pagado' => $val_pagado + $val_pag,
            ];
            if ($detalle_mes == 1) {
                $val_adicion_mes = $f['val_adicion_mes'];
                $val_reduccion_mes = $f['val_reduccion_mes'];
                $val_credito_mes = $f['val_credito_mes'];
                $val_contracredito_mes = $f['val_contracredito_mes'];
                $val_comprometido_mes = $f['val_comprometido_mes'];
                $val_registrado_mes = $f['val_registrado_mes'];
                $val_causado_mes = $f['val_causado_mes'];
                $val_pagado_mes = $f['val_pagado_mes'];
                $val_ad_mes = isset($acum[$rubro]['adicion_mes']) ? $acum[$rubro]['adicion_mes'] : 0;
                $val_red_mes = isset($acum[$rubro]['reduccion_mes']) ? $acum[$rubro]['reduccion_mes'] : 0;
                $val_cre_mes = isset($acum[$rubro]['credito_mes']) ? $acum[$rubro]['credito_mes'] : 0;
                $val_ccre_mes = isset($acum[$rubro]['contracredito_mes']) ? $acum[$rubro]['contracredito_mes'] : 0;
                $val_cdp_mes = isset($acum[$rubro]['comprometido_mes']) ? $acum[$rubro]['comprometido_mes'] : 0;
                $val_crp_mes = isset($acum[$rubro]['regitrado_mes']) ? $acum[$rubro]['regitrado_mes'] : 0;
                $val_cop_mes = isset($acum[$rubro]['causado_mes']) ? $acum[$rubro]['causado_mes'] : 0;
                $val_pag_mes = isset($acum[$rubro]['pagado_mes']) ? $acum[$rubro]['pagado_mes'] : 0;
                $acum[$rubro] += [
                    'adicion_mes' => $val_adicion_mes + $val_ad_mes,
                    'reduccion_mes' => $val_reduccion_mes + $val_red_mes,
                    'credito_mes' => $val_credito_mes + $val_cre_mes,
                    'contracredito_mes' => $val_contracredito_mes + $val_ccre_mes,
                    'comprometido_mes' => $val_comprometido_mes + $val_cdp_mes,
                    'regitrado_mes' => $val_registrado_mes + $val_crp_mes,
                    'causado_mes' => $val_causado_mes + $val_cop_mes,
                    'pagado_mes' => $val_pagado_mes + $val_pag_mes,
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
            <td colspan="23" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
        </tr>
        <tr>
            <td colspan="23" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
        </tr>
        <tr>
            <td colspan="23" style="text-align:center"><?php echo 'EJECUCION PRESUPUESTAL DE GASTOS'; ?></td>
        </tr>
        <tr>
            <td colspan="23" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
        </tr>
        <tr style="background-color: #CED3D3; text-align:center;font-size:9px;">
            <th>Descripcion</th>
            <th>Rubro</th>
            <th>Estado</th>
            <th>Tipo</th>
            <th>Presupuesto inicial</th>
            <?php if ($detalle_mes == 1) {
                echo '<th>Adiciones mes</th>';
            } ?>
            <th>Adiciones</th>
            <?php if ($detalle_mes == 1) {
                echo '<th>Reducciones mes</th>';
            } ?>
            <th>Reducciones</th>
            <?php if ($detalle_mes == 1) {
                echo '<th>Cr&eacute;ditos mes</th>';
            } ?>
            <th>Cr&eacute;ditos</th>
            <?php if ($detalle_mes == 1) {
                echo '<th>Contracreditos mes</th>';
            } ?>
            <th>Contracreditos</th>
            <th>Presupuesto definitivo</th>
            <?php if ($detalle_mes == 1) {
                echo '<th>Disponibilidades mes</th>';
            } ?>
            <th>Disponibilidades</th>
            <?php if ($detalle_mes == 1) {
                echo '<th>Compromisos mes</th>';
            } ?>
            <th>Compromisos</th>
            <?php if ($detalle_mes == 1) {
                echo '<th>Obligaciones mes</th>';
            } ?>
            <th>Obligación</th>
            <?php if ($detalle_mes == 1) {
                echo '<th>Pagos mes</th>';
            } ?>
            <th>Pagos</th>
            <th>Saldo presupuestal</th>
            <th>Cuentas por pagar</th>
        </tr>
    </thead>
    <tbody style="font-size:9px;">
        <?php
        foreach ($acum as $key => $value) {
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
            if ($value['regitrado'] == 0) {
                $ciento = 0;
            } else {
                $ciento = $value['comprometido'] / $value['regitrado'];
            }
            if ($ciento >= 0 && $ciento <= 0.4) {
                $color = '#2ECC71';
            } else if ($ciento > 0.4 && $ciento <= 0.7) {
                $color = '#F1C40F';
            } else if ($ciento > 0.7 && $ciento <= 0.9) {
                $color = '#E67E22';
            } else {
                $color = '#E74C3C';
            }
            echo '<tr class="resaltar">';
            echo '<td class="text">' . $key . '</td>';
            echo '<td class="text">' . $nomrb . '</td>';
            echo '<td class="text border border-light" style="background-color:' . $color . '"></td>';
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
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . number_format($value['credito_mes'], 2, ".", ",") . '</td>';
            }
            echo '<td style="text-align:right">' . number_format($value['credito'], 2, ".", ",") . '</td>';
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . number_format($value['contracredito_mes'], 2, ".", ",") . '</td>';
            }
            echo '<td style="text-align:right">' . number_format($value['contracredito'], 2, ".", ",") . '</td>';
            echo '<td style="text-align:right">' . number_format(($value['inicial'] + $value['adicion'] - $value['reduccion'] + $value['credito'] - $value['contracredito']), 2, ".", ",") . '</td>';
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . number_format($value['comprometido_mes'], 2, ".", ",") . '</td>';
            }
            echo '<td style="text-align:right">' . number_format($value['comprometido'], 2, ".", ",") . '</td>';
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . number_format($value['regitrado_mes'], 2, ".", ",") . '</td>';
            }
            echo '<td style="text-align:right">' . number_format($value['regitrado'], 2, ".", ",") . '</td>';
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . number_format($value['causado_mes'], 2, ".", ",") . '</td>';
            }
            echo '<td style="text-align:right">' . number_format($value['causado'], 2, ".", ",") . '</td>';
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . number_format($value['pagado_mes'], 2, ".", ",") . '</td>';
            }
            echo '<td style="text-align:right">' . number_format($value['pagado'], 2, ".", ",") . '</td>';
            echo '<td style="text-align:right">' . number_format((($value['inicial'] + $value['adicion'] - $value['reduccion'] + $value['credito'] - $value['contracredito']) - $value['comprometido']), 2, ".", ",") . '</td>';
            echo '<td style="text-align:right">' . number_format(($value['causado'] - $value['pagado']), 2, ".", ",") . '</td>';
            echo '</tr>';
        }
        ?>
    </tbody>
</table>