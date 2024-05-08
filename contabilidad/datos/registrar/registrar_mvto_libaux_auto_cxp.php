<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
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
                INNER JOIN `ctt_adquisiciones` 
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
                        INNER JOIN `ctt_adquisiciones` 
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
                INNER JOIN `ctt_adquisiciones` 
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
                        INNER JOIN `ctt_adquisiciones` 
                            ON (`ctt_adquisiciones`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
                    WHERE (`ctb_libaux`.`credito` > 0)
                    GROUP BY `ctt_adquisiciones`.`id_tipo_bn_sv`))";
    $rs = $cmd->query($query);
    $ctas_credito = $rs->fetchAll();
    // Consulto en la tabla de costos cuantos registros tiene asociados
    $sq2 = "SELECT
                `ctb_causa_costos`.`valor`
                , `ctt_adquisiciones`.`id_tipo_bn_sv`
            FROM
                `ctb_causa_costos`
                INNER JOIN `pto_cop_detalle` 
                    ON (`ctb_causa_costos`.`id_ctb_doc` = `pto_cop_detalle`.`id_ctb_doc`)
                INNER JOIN `pto_crp_detalle` 
                    ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                INNER JOIN `pto_cdp_detalle` 
                    ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                INNER JOIN `pto_cdp` 
                    ON (`pto_cdp_detalle`.`id_pto_cdp` = `pto_cdp`.`id_pto_cdp`)
                INNER JOIN `ctt_adquisiciones` 
                    ON (`ctt_adquisiciones`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
            WHERE (`ctb_causa_costos`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sq2);
    $datoscostos = $rs->fetchAll();

    // Consulto las retenciones causadas en ctb_causa_retencion
    $sq2 = "SELECT
                `ctb_causa_retencion`.`valor_retencion`
                , `ctb_causa_retencion`.`id_terceroapi`
                , `ctb_retencion_rango`.`id_retencion`
            FROM
                `ctb_causa_retencion`
                INNER JOIN `ctb_retencion_rango` 
                    ON (`ctb_causa_retencion`.`id_rango` = `ctb_retencion_rango`.`id_rango`)
            WHERE (`ctb_causa_retencion`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sq2);
    $datosretencion = $rs->fetchAll();
    $sqln = "SELECT
                `ctb_libaux`.`id_ctb_libaux`
                , `ctb_retencion_rango`.`id_retencion`
                , `ctb_libaux`.`id_cuenta`
            FROM
                `ctb_causa_retencion`
                INNER JOIN `ctb_retencion_rango` 
                    ON (`ctb_causa_retencion`.`id_rango` = `ctb_retencion_rango`.`id_rango`)
                INNER JOIN `ctb_doc` 
                    ON (`ctb_causa_retencion`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `ctb_libaux` 
                    ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
            WHERE (`ctb_libaux`.`id_ctb_libaux` 
                IN (SELECT
                        MAX(`ctb_libaux`.`id_ctb_libaux`)
                    FROM
                        `ctb_causa_retencion`
                        INNER JOIN `ctb_retencion_rango` 
                            ON (`ctb_causa_retencion`.`id_rango` = `ctb_retencion_rango`.`id_rango`)
                        INNER JOIN `ctb_doc` 
                            ON (`ctb_causa_retencion`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        INNER JOIN `ctb_libaux` 
                            ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    GROUP BY `ctb_retencion_rango`.`id_retencion`)
                )";
    $rs = $cmd->query($sqln);
    $ctas_ret = $rs->fetchAll();
    $credito = 0;
    $acumulador = 0;
    $query = "INSERT INTO `ctb_libaux`
	            (`id_ctb_doc`,`id_tercero_api`,`id_cuenta`,`debito`,`credito`,`id_user_reg`,`fecha_reg`)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_doc, PDO::PARAM_INT);
    $query->bindParam(2, $id_tercero, PDO::PARAM_INT);
    $query->bindParam(3, $id_cuenta, PDO::PARAM_INT);
    $query->bindParam(4, $debito, PDO::PARAM_STR);
    $query->bindParam(5, $credito, PDO::PARAM_STR);
    $query->bindParam(6, $iduser, PDO::PARAM_INT);
    $query->bindParam(7, $fecha2);
    $total_debito = 0;
    $total_credito = 0;
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
    $debito = 0;
    foreach ($datosretencion as $dr) {
        $id_rte = $dr['id_retencion'];
        $key = array_search($id_rte, array_column($ctas_ret, 'id_retencion'));
        $id_cuenta = $key !== false ? $ctas_ret[$key]['id_cuenta'] : NULL;
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
    foreach ($datoscostos as $dc) {
        $id_tipo_bn_sv = $dc['id_tipo_bn_sv'];
        $key = array_search($id_tipo_bn_sv, array_column($ctas_credito, 'id_tipo_bn_sv'));
        $id_cuenta = $key !== false ? $ctas_credito[$key]['id_cuenta'] : NULL;
        $credito = $total_debito - $total_credito;
        $id_tercero = $id_tercero_ant;
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $acumulador++;
        } else {
            $response['msg'] = $query->errorInfo()[2];
        }
        break;
    }
} catch (PDOException $e) {
    $response['msg'] = $e->getMessage();
}
if ($acumulador > 0) {
    $response['status'] = 'ok';
}
echo json_encode($response);
exit();
if (isset($_POST)) {
    // Busco en el tipo de bien o servicio que corresponde

    foreach ($datosretencion as $key => $value) {
        $id_rte = $value['id_causa_retencion'];
        $credito = $value['valor_retencion'];
        $id_terceroapi = $value['id_terceroapi'];
        // consultar el id del tercero refernciado por el id_terceroapi
        $sql = $cmd->prepare("SELECT id_tercero FROM seg_terceros WHERE id_tercero_api = ? ;");
        $sql->bindParam(1, $id_terceroapi, PDO::PARAM_INT);
        $sql->execute();
        $result = $sql->fetch();
        $id_tercero = $result['id_tercero'];
        // Consultar el numero de cuenta para un registro similiar
        $sql = $cmd->prepare("SELECT cuenta FROM ctb_libaux WHERE id_rte = ? ;");
        $sql->bindParam(1, $id_rte, PDO::PARAM_INT);
        $sql->execute();
        $result = $sql->fetch();
        $cuenta = $result['cuenta'];
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $response[] = array("value" => 'ok');
        } else {
            $response[] = array("value" => 'Error del insert');
            print_r($query->errorInfo()[2]);
        }
    }
    // Liquido el valor de la cuenta por pagar, busco la diferencia debito credito en el libro auxiliar para el documento
    $sql = $cmd->prepare("SELECT SUM(debito) AS debito, SUM(credito) AS credito FROM ctb_libaux WHERE id_ctb_doc = ? ;");
    $sql->bindParam(1, $id_doc, PDO::PARAM_INT);
    $sql->execute();
    $result = $sql->fetch();
    $deb = $result['debito'];
    $cre = $result['credito'];
    $credito = $deb - $cre;
    $valor = 0;
    $id_cc = 0;
    $id_rte = 0;
    // buscar id_cta_factura
    $sql = $cmd->prepare("SELECT id_cta_factura FROM seg_ctb_factura WHERE id_ctb_doc = ? ;");
    $sql->bindParam(1, $id_doc, PDO::PARAM_INT);
    $sql->execute();
    $result = $sql->fetch();
    $id_fac = $result['id_cta_factura'];
    // buscar la cuenta asociada al tipo de cuenta por pagar 
    $sql = $cmd->prepare("SELECT cuenta FROM ctb_libaux WHERE id_tipo_ad = ? AND id_fac >0;");
    $sql->bindParam(1, $id_tipo_bn_sv, PDO::PARAM_INT);
    $sql->execute();
    $result = $sql->fetch();
    $cuenta = $result['cuenta'];
    $id_tercero = $id_tercero_ant;
    $query->execute();
    if ($cmd->lastInsertId() > 0) {
        $response[] = array("value" => 'ok');
    } else {
        $response[] = array("value" => 'Error del insert');
        print_r($query->errorInfo()[2]);
    }


    echo json_encode($response);
}
