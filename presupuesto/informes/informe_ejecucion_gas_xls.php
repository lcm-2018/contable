<?php
session_start();
set_time_limit(5600);
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
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
    return number_format($valor, 2, ".", ",");
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
                    WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini_mes' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND `pto_mod`.`id_tipo_mod` = 2)
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
                    WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini_mes' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND (`pto_mod`.`id_tipo_mod` = 6 OR `pto_mod`.`id_tipo_mod` = 1) AND `pto_presupuestos`.`id_tipo` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `credito_mes`
                    ON(`credito_mes`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_cred`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                            ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                        INNER JOIN `pto_presupuestos` 
                            ON (`pto_mod`.`id_pto` = `pto_presupuestos`.`id_pto`)
                    WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini_mes' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND (`pto_mod`.`id_tipo_mod` = 6 OR `pto_mod`.`id_tipo_mod` = 1) AND `pto_presupuestos`.`id_tipo` = 2)
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
                , `pto_presupuestos`.`id_tipo`
                $valores_mes
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
                    WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND (`pto_mod`.`id_tipo_mod` = 6 OR `pto_mod`.`id_tipo_mod` = 1) AND `pto_presupuestos`.`id_tipo` = 2)
                    GROUP BY `pto_mod_detalle`.`id_cargue`) AS `credito`
                    ON(`credito`.`id_cargue` = `pto_cargue`.`id_cargue`)
                LEFT JOIN
                    (SELECT
                        `pto_mod_detalle`.`id_cargue`
                        , SUM(`pto_mod_detalle`.`valor_cred`) AS `valor`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                            ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                        INNER JOIN `pto_presupuestos` 
                            ON (`pto_mod`.`id_pto` = `pto_presupuestos`.`id_pto`)
                    WHERE (`pto_mod`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `pto_mod`.`estado` = 2 AND (`pto_mod`.`id_tipo_mod` = 6 OR `pto_mod`.`id_tipo_mod` = 1) AND `pto_presupuestos`.`id_tipo` = 2)
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
                    $join_mes
                   WHERE (`pto_presupuestos`.`id_tipo` = 2)
                   ORDER BY `pto_cargue`.`cod_pptal` ASC";
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$acum = [];
foreach ($rubros as $rb) {
    $rubro = $rb['cod_pptal'];

    // Filtrar los rubros que comienzan con el mismo código
    $filtro = array_filter($rubros, function ($item) use ($rubro) {
        return strpos($item['cod_pptal'], $rubro) === 0;
    });

    if (!empty($filtro)) {
        // Inicializar el array de acumulación
        $acum[$rubro] = [
            'inicial' => 0,
            'adicion' => 0,
            'reduccion' => 0,
            'credito' => 0,
            'contracredito' => 0,
            'comprometido' => 0,
            'registrado' => 0, // Corregido
            'causado' => 0,
            'pagado' => 0,
            'adicion_mes' => 0,
            'reduccion_mes' => 0,
            'credito_mes' => 0,
            'contracredito_mes' => 0,
            'comprometido_mes' => 0,
            'registrado_mes' => 0,
            'causado_mes' => 0,
            'pagado_mes' => 0,
        ];

        foreach ($filtro as $f) {
            if ($f['tipo_dato'] == 1) {
                // Acumulación de valores generales
                $acum[$rubro]['inicial'] += $f['inicial'];
                $acum[$rubro]['adicion'] += $f['val_adicion'];
                $acum[$rubro]['reduccion'] += $f['val_reduccion'];
                $acum[$rubro]['credito'] += $f['val_credito'];
                $acum[$rubro]['contracredito'] += $f['val_contracredito'];
                $acum[$rubro]['comprometido'] += $f['val_comprometido'];
                $acum[$rubro]['registrado'] += $f['val_registrado'];
                $acum[$rubro]['causado'] += $f['val_causado'];
                $acum[$rubro]['pagado'] += $f['val_pagado'];

                // Acumulación de valores mensuales
                if ($detalle_mes == 1) {
                    $acum[$rubro]['adicion_mes'] += $f['val_adicion_mes'];
                    $acum[$rubro]['reduccion_mes'] += $f['val_reduccion_mes'];
                    $acum[$rubro]['credito_mes'] += $f['val_credito_mes'];
                    $acum[$rubro]['contracredito_mes'] += $f['val_contracredito_mes'];
                    $acum[$rubro]['comprometido_mes'] += $f['val_comprometido_mes'];
                    $acum[$rubro]['registrado_mes'] += $f['val_registrado_mes'];
                    $acum[$rubro]['causado_mes'] += $f['val_causado_mes'];
                    $acum[$rubro]['pagado_mes'] += $f['val_pagado_mes'];
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
<table style="width:100% !important; border-collapse: collapse; font-size:9px">
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
        <tr style="background-color: #CED3D3; text-align:center;">
            <th>Descripcion</th>
            <th>Rubro</th>
            <th>Estado</th>
            <th>Tipo</th>
            <th>Presupuesto inicial</th>
            <?= $detalle_mes == 1 ? '<th>Adiciones mes</th>' : ''; ?>
            <th>Adiciones</th>
            <?= $detalle_mes == 1 ? '<th>Reducciones mes</th>' : ''; ?>
            <th>Reducciones</th>
            <?= $detalle_mes == 1 ? '<th>Créditos mes</th>' : ''; ?>
            <th>Créditos</th>
            <?= $detalle_mes == 1 ? '<th>Contracréditos mes</th>' : ''; ?>
            <th>Contracreditos</th>
            <th>Presupuesto definitivo</th>
            <?= $detalle_mes == 1 ? '<th>Compromisos mes</th>' : ''; ?>
            <th>Disponibilidades</th>
            <?= $detalle_mes == 1 ? '<th>Registrados mes</th>' : ''; ?>
            <th>Compromisos</th>
            <?= $detalle_mes == 1 ? '<th>Causados mes</th>' : ''; ?>
            <th>Obligación</th>
            <?= $detalle_mes == 1 ? '<th>Pagados mes</th>' : ''; ?>
            <th>Pagos</th>
            <th>Saldo presupuestal</th>
            <th>Cuentas por pagar</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($acum as $key => $value) {
            $keyrb = array_search($key, array_column($rubros, 'cod_pptal'));
            if ($keyrb !== false) {
                $nomrb = $rubros[$keyrb]['nom_rubro'];
                $tipo = $rubros[$keyrb]['tipo_dato'];
            } else {
                $nomrb = '';
            }
            $tipo_dat = $tipo == '0' ? 'M' : 'D';

            if ($value['registrado'] == 0) {
                $ciento = 0;
            } else {
                $ciento = $value['comprometido'] / $value['registrado'];
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
            echo '<td style="text-align:right">' . pesos($value['inicial']) . '</td>';
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . pesos($value['adicion_mes']) . '</td>';
            }
            echo '<td style="text-align:right">' . pesos($value['adicion']) . '</td>';
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . pesos($value['reduccion_mes']) . '</td>';
            }
            echo '<td style="text-align:right">' . pesos($value['reduccion']) . '</td>';
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . pesos($value['credito_mes']) . '</td>';
            }
            echo '<td style="text-align:right">' . pesos($value['credito']) . '</td>';
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . pesos($value['contracredito_mes']) . '</td>';
            }
            echo '<td style="text-align:right">' . pesos($value['contracredito']) . '</td>';
            echo '<td style="text-align:right">' . pesos(($value['inicial'] + $value['adicion'] - $value['reduccion'] + $value['credito'] - $value['contracredito'])) . '</td>';
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . pesos($value['comprometido_mes']) . '</td>';
            }
            echo '<td style="text-align:right">' . pesos($value['comprometido']) . '</td>';
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . pesos($value['registrado_mes']) . '</td>';
            }
            echo '<td style="text-align:right">' . pesos($value['registrado']) . '</td>';
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . pesos($value['causado_mes']) . '</td>';
            }
            echo '<td style="text-align:right">' . pesos($value['causado']) . '</td>';
            if ($detalle_mes == 1) {
                echo '<td style="text-align:right">' . pesos($value['pagado_mes']) . '</td>';
            }
            echo '<td style="text-align:right">' . pesos($value['pagado']) . '</td>';
            echo '<td style="text-align:right">' . pesos((($value['inicial'] + $value['adicion'] - $value['reduccion'] + $value['credito'] - $value['contracredito']) - $value['comprometido'])) . '</td>';
            echo '<td style="text-align:right">' . pesos(($value['causado'] - $value['pagado'])) . '</td>';
            echo '</tr>';
        }
        ?>
    </tbody>
</table>