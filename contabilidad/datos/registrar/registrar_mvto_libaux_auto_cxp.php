<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
$_post = json_decode(file_get_contents('php://input'), true);
include_once '../../../conexion.php';
include_once '../../../permisos.php';
include_once '../../../financiero/consultas.php';

$id_doc = $_post['id_doc'];
$id_crp = $_post['id_crp'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$response['status'] = 'error';
$response['msg'] = '<br>Ningún registro afectado';
$datosDoc = GetValoresCxP($id_doc, $cmd);
try {
    $query = "SELECT `id_ctb_libaux` FROM `ctb_libaux` WHERE `id_ctb_doc` = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_doc, PDO::PARAM_INT);
    $query->execute();
    $datos = $query->fetch();
    if (!empty($datos)) {
        $query = $cmd->prepare("DELETE FROM `ctb_libaux` WHERE `id_ctb_doc` = ?");
        $query->bindParam(1, $id_doc, PDO::PARAM_INT);
        $query->execute();
    }
    $id_tercero = $datosDoc['id_tercero'];
    $id_tercero_ant =  $id_tercero;
    $query = "SELECT
                `ctb_libaux`.`id_cuenta`
                , `ctt_adquisiciones`.`id_tipo_bn_sv`
            FROM
                `ctb_libaux`
                INNER JOIN `ctb_doc` 
                    ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `pto_cop_detalle` 
                    ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `pto_crp_detalle` 
                    ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                INNER JOIN `pto_cdp_detalle` 
                    ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                INNER JOIN `pto_cdp` 
                    ON (`pto_cdp_detalle`.`id_pto_cdp` = `pto_cdp`.`id_pto_cdp`)
                LEFT JOIN `ctt_adquisiciones` 
                    ON (`ctt_adquisiciones`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
            WHERE (`ctb_libaux`.`id_ctb_libaux` 
                IN (SELECT
                        MAX(`ctb_libaux`.`id_ctb_libaux`) AS `id_libaux`
                    FROM
                        `ctb_libaux`
                        INNER JOIN `ctb_doc` 
                            ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        INNER JOIN `pto_cop_detalle` 
                            ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        INNER JOIN `pto_crp_detalle` 
                            ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                        INNER JOIN `pto_cdp_detalle` 
                            ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                        INNER JOIN `pto_cdp` 
                            ON (`pto_cdp_detalle`.`id_pto_cdp` = `pto_cdp`.`id_pto_cdp`)
                        LEFT JOIN `ctt_adquisiciones` 
                            ON (`ctt_adquisiciones`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
                    WHERE (`ctb_libaux`.`debito` > 0)
                    GROUP BY `ctt_adquisiciones`.`id_tipo_bn_sv`))";
    $rs = $cmd->query($query);
    $ctas_debito = $rs->fetchAll();
    $query = "SELECT
                `ctb_libaux`.`id_cuenta`
                , `ctt_adquisiciones`.`id_tipo_bn_sv`
            FROM
                `ctb_libaux`
                INNER JOIN `ctb_doc` 
                    ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `pto_cop_detalle` 
                    ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `pto_crp_detalle` 
                    ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                INNER JOIN `pto_cdp_detalle` 
                    ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                INNER JOIN `pto_cdp` 
                    ON (`pto_cdp_detalle`.`id_pto_cdp` = `pto_cdp`.`id_pto_cdp`)
                LEFT JOIN `ctt_adquisiciones` 
                    ON (`ctt_adquisiciones`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
            WHERE (`ctb_libaux`.`id_ctb_libaux` 
                IN (SELECT
                        MAX(`ctb_libaux`.`id_ctb_libaux`) AS `id_libaux`
                    FROM
                        `ctb_libaux`
                        INNER JOIN `ctb_doc` 
                            ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        INNER JOIN `pto_cop_detalle` 
                            ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        INNER JOIN `pto_crp_detalle` 
                            ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                        INNER JOIN `pto_cdp_detalle` 
                            ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                        INNER JOIN `pto_cdp` 
                            ON (`pto_cdp_detalle`.`id_pto_cdp` = `pto_cdp`.`id_pto_cdp`)
                        LEFT JOIN `ctt_adquisiciones` 
                            ON (`ctt_adquisiciones`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
                    WHERE (`ctb_libaux`.`credito` > 0)
                    GROUP BY `ctt_adquisiciones`.`id_tipo_bn_sv`))";
    $rs = $cmd->query($query);
    $ctas_credito = $rs->fetchAll();
    // Consulto en la tabla de costos cuantos registros tiene asociados
    $sq2 = "SELECT
                `ctt_adquisiciones`.`id_tipo_bn_sv`
                , `ctb_causa_costos`.`valor`
            FROM
                `ctb_doc`
                INNER JOIN `pto_crp` 
                    ON (`ctb_doc`.`id_crp` = `pto_crp`.`id_pto_crp`)
                LEFT JOIN `ctt_adquisiciones` 
                    ON (`pto_crp`.`id_cdp` = `ctt_adquisiciones`.`id_cdp`)
                INNER JOIN `ctb_causa_costos` 
                    ON (`ctb_causa_costos`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sq2);
    $datoscostos = $rs->fetchAll();

    // Consulto las retenciones causadas en ctb_causa_retencion
    $sq2 = "SELECT
                `ctb_causa_retencion`.`valor_retencion`
                , `ctb_causa_retencion`.`id_terceroapi`
                , `ctb_retencion_rango`.`id_retencion`
                , `ctb_retenciones`.`id_cuenta`
            FROM
                `ctb_causa_retencion`
                INNER JOIN `ctb_retencion_rango` 
                    ON (`ctb_causa_retencion`.`id_rango` = `ctb_retencion_rango`.`id_rango`)
                LEFT JOIN `ctb_retenciones`
                    ON (`ctb_retencion_rango`.`id_retencion` = `ctb_retenciones`.`id_retencion`)
            WHERE (`ctb_causa_retencion`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sq2);
    $datosretencion = $rs->fetchAll();

    $sqla = "SELECT
                SUM(`vl`.`cantidad` * `vl`.`valor_sin_iva`) AS `base`
                , SUM(`vl`.`cantidad`*`vl`.`valor_sin_iva` * `vl`.`iva`/100) AS `iva`
                , `vl`.`id_cuenta` 
            FROM	
                (SELECT
                    `far_orden_ingreso_detalle`.`cantidad`
                    ,`far_orden_ingreso_detalle`.`valor_sin_iva`
                    , `far_orden_ingreso_detalle`.`iva`
                    , `far_orden_ingreso_detalle`.`id_ingreso`
                    , `taux`.`id_cuenta`
                FROM
                    `far_orden_ingreso_detalle`
                    INNER JOIN `far_orden_ingreso` 
                        ON (`far_orden_ingreso_detalle`.`id_ingreso` = `far_orden_ingreso`.`id_ingreso`)
                    INNER JOIN `far_medicamento_lote` 
                        ON (`far_orden_ingreso_detalle`.`id_lote` = `far_medicamento_lote`.`id_lote`)
                    INNER JOIN `far_medicamentos` 
                        ON (`far_medicamento_lote`.`id_med` = `far_medicamentos`.`id_med`)
                    LEFT JOIN `far_subgrupos` 
                        ON (`far_medicamentos`.`id_subgrupo` = `far_subgrupos`.`id_subgrupo`)
                    LEFT JOIN
                        (SELECT 
                            `id_subgrupo`,`id_cuenta`
                        FROM `far_subgrupos_cta`
                        WHERE `id_subgrupo_cta` IN 
                            (SELECT 
                                MAX(`id_subgrupo_cta`) 
                            FROM `far_subgrupos_cta` 
                            WHERE `fecha_vigencia` <= DATE_FORMAT(NOW(), '%Y-%m-%d') 
                            GROUP BY `id_subgrupo`)
                        ) AS `taux`
                    ON (`taux`.`id_subgrupo` = `far_subgrupos`.`id_subgrupo`)
                WHERE (`far_orden_ingreso`.`id_ctb_doc` = $id_doc)) AS `vl`
            GROUP BY `vl`.`id_cuenta`";
    $rs = $cmd->query($sqla);
    $ingresos = $rs->fetchAll(PDO::FETCH_ASSOC);
    $credito = 0;
    $acumulador = 0;
    $ref = 0;
    $query = "INSERT INTO `ctb_libaux`
	            (`id_ctb_doc`,`id_tercero_api`,`id_cuenta`,`debito`,`credito`,`id_user_reg`,`fecha_reg`,`ref`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_doc, PDO::PARAM_INT);
    $query->bindParam(2, $id_tercero, PDO::PARAM_INT);
    $query->bindParam(3, $id_cuenta, PDO::PARAM_INT);
    $query->bindParam(4, $debito, PDO::PARAM_STR);
    $query->bindParam(5, $credito, PDO::PARAM_STR);
    $query->bindParam(6, $iduser, PDO::PARAM_INT);
    $query->bindParam(7, $fecha2);
    $query->bindParam(8, $ref, PDO::PARAM_INT);
    $total_debito = 0;
    $total_credito = 0;
    if (empty($ingresos)) {
        foreach ($datoscostos as $dc) {
            $id_tipo_bn_sv = $dc['id_tipo_bn_sv'];
            $key = array_search($id_tipo_bn_sv, array_column($ctas_debito, 'id_tipo_bn_sv'));
            $id_cuenta = $key !== false ? $ctas_debito[$key]['id_cuenta'] : NULL;
            $debito = $dc['valor'];
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $total_debito += $debito;
                $acumulador++;
            } else {
                $response['msg'] = $query->errorInfo()[2];
            }
        }
    } else {
        foreach ($ingresos as $ingreso) {
            $id_cuenta = $ingreso['id_cuenta'];
            $debito = $ingreso['base'] + $ingreso['iva'];
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $total_debito += $debito;
                $acumulador++;
            } else {
                $response['msg'] = $query->errorInfo()[2];
            }
        }
    }
    $debito = 0;
    foreach ($datosretencion as $dr) {
        $id_rte = $dr['id_retencion'];
        $id_cuenta = $dr['id_cuenta'];
        $credito = $dr['valor_retencion'];
        $id_tercero = $dr['id_terceroapi'];
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $total_credito += $credito;
            $acumulador++;
        } else {
            $response['msg'] = $query->errorInfo()[2];
        }
    }
    if (empty($ingresos)) {
        foreach ($datoscostos as $dc) {
            $id_tipo_bn_sv = $dc['id_tipo_bn_sv'];
            $key = array_search($id_tipo_bn_sv, array_column($ctas_credito, 'id_tipo_bn_sv'));
            $id_cuenta = $key !== false ? $ctas_credito[$key]['id_cuenta'] : NULL;
            $credito = $total_debito - $total_credito;
            $id_tercero = $id_tercero_ant;
            $ref = 1;
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $acumulador++;
            } else {
                $response['msg'] = $query->errorInfo()[2];
            }
            break;
        }
    } else {
        $credito = $total_debito - $total_credito;
        $id_tercero = $id_tercero_ant;
        $ref = 1;
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $acumulador++;
        } else {
            $response['msg'] = $query->errorInfo()[2];
        }
    }
} catch (PDOException $e) {
    $response['msg'] = $e->getMessage();
}
if ($acumulador > 0) {
    $response['status'] = 'ok';
}
echo json_encode($response);
exit();
