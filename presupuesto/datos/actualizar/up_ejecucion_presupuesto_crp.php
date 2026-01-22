<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_crp = isset($_POST['id_crp']) ? $_POST['id_crp'] : exit('Acceso no disponible');
$id_pto = $_POST['id_pto'];
$fecha = $_POST['dateFecha'];
$num_solicitud = $_POST['txtContrato'];
$id_manu = $_POST['id_manu'];
$id_tercero = $_POST['id_tercero'];
$id_teractual = $_POST['id_teractual'];
$id_adq = $_POST['id_adq'];
$objeto = $_POST['txtObjeto'];
$id_user = $_SESSION['id_user'];
$tesoreria = isset($_POST['chkTesoreria']) ? 1 : 0;
$id_vigencia = $_SESSION['id_vigencia'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$reponse['status'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_manu` 
            FROM
                `pto_crp`
            WHERE (`id_pto` = $id_pto AND `id_manu` = $id_manu AND `id_pto_crp` <> $id_crp)";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    if (!empty($consecutivo)) {
        $response['msg'] = 'El consecutivo de RP <b>' . $id_manu . '</b> ya se encuentra registrado';
        echo json_encode($response);
        exit();
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `pto_crp_detalle`.`id_pto_crp_det`
                , `pto_crp_detalle`.`id_tercero_api`
                , `pto_crp_detalle`.`valor`
                , `pto_cdp_detalle`.`id_rubro`
            FROM
                `pto_crp_detalle`
                INNER JOIN `pto_cdp_detalle` 
                    ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
            WHERE (`pto_crp_detalle`.`id_pto_crp` = $id_crp)";
    $rs = $cmd->query($sql);
    $datosCRP = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$tescon = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
            FROM
                `ctb_doc`
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
            WHERE (`ctb_doc`.`id_crp` = $id_crp AND `ctb_fuente`.`cod` = 'CXPA' AND `ctb_doc`.`estado` > 0)";
    $rs = $cmd->query($sql);
    $causaciones = $rs->fetchAll(PDO::FETCH_ASSOC);
    if ($tesoreria == 0 && !empty($causaciones)) {
        $query = "UPDATE `ctb_doc` SET `estado` = 0 WHERE `id_ctb_doc` = ?";
        $query = $cmd->prepare($query);
        foreach ($causaciones as $causacion) {
            $query->bindParam(1, $causacion['id_ctb_doc'], PDO::PARAM_INT);
            $query->execute();
            if ($query->rowCount() > 0) {
                $tescon++;
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if ($tesoreria == 1) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT 
                `id_doc_fuente`,`cod`,`id_manu`
            FROM 
                `ctb_fuente`
                LEFT JOIN
                        (SELECT 
                            MAX(`id_manu`) AS `id_manu`,`id_tipo_doc`
                        FROM 
                            `ctb_doc`
                        WHERE `id_vigencia` = $id_vigencia
                        GROUP BY `id_tipo_doc`) AS `t1`
                    ON (`t1`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
            WHERE `cod` = 'CXPA'";
        $rs = $cmd->query($sql);
        $consecutivo = $rs->fetch();
        if (empty($consecutivo)) {
            $response['msg'] = 'No se ha configurado el consecutivo de Causación de Pago directos a Tesorería <b>CXPA</b>';
            echo json_encode($response);
            exit();
        } else {
            $id_doc_fuente = $consecutivo['id_doc_fuente'];
            $id_consec = $consecutivo['id_manu'] != '' ? $consecutivo['id_manu'] + 1 : 1;
        }
        $cerrado = 2;
        $sql2 = "INSERT INTO `ctb_doc`
                    (`id_vigencia`,`id_tipo_doc`,`id_manu`,`id_tercero`,`fecha`,`detalle`,`estado`,`id_user_reg`,`fecha_reg`,`id_crp`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql2 = $cmd->prepare($sql2);
        $sql2->bindParam(1, $id_vigencia, PDO::PARAM_INT);
        $sql2->bindParam(2, $id_doc_fuente, PDO::PARAM_INT);
        $sql2->bindParam(3, $id_consec, PDO::PARAM_INT);
        $sql2->bindParam(4, $id_tercero, PDO::PARAM_INT);
        $sql2->bindParam(5, $fecha, PDO::PARAM_STR);
        $sql2->bindParam(6, $objeto, PDO::PARAM_STR);
        $sql2->bindParam(7, $cerrado, PDO::PARAM_INT);
        $sql2->bindParam(8, $id_user, PDO::PARAM_INT);
        $sql2->bindValue(9, $date->format('Y-m-d H:i:s'));
        $sql2->bindParam(10, $id_crp, PDO::PARAM_INT);
        $sql2->execute();
        $id_docts = $cmd->lastInsertId();
        if (!($id_docts > 0)) {
            $response['msg'] = $sql2->errorInfo()[2];
            echo json_encode($response);
            exit();
        }
        $liberado = 0;
        $query2 = "INSERT INTO `pto_cop_detalle`
                    (`id_ctb_doc`, `id_pto_crp_det`, `id_tercero_api`, `valor`, `valor_liberado`, `id_user_reg`, `fecha_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $query2 = $cmd->prepare($query2);
        $query2->bindParam(1, $id_docts, PDO::PARAM_INT);
        $query2->bindParam(2, $id_crp_det, PDO::PARAM_INT);
        $query2->bindParam(3, $tercero, PDO::PARAM_INT);
        $query2->bindParam(4, $valor, PDO::PARAM_STR);
        $query2->bindParam(5, $liberado, PDO::PARAM_STR);
        $query2->bindParam(6, $id_user, PDO::PARAM_INT);
        $query2->bindValue(7, $date->format('Y-m-d H:i:s'));
        foreach ($datosCRP as $dato) {
            $id_crp_det = $dato['id_pto_crp_det'];
            $tercero = $dato['id_tercero_api'];
            $valor = $dato['valor'];
            $query2->execute();
            if ($query2->rowCount() > 0) {
                $tescon++;
            } else {
                $response['msg'] = $query2->errorInfo()[2];
                echo json_encode($response);
                exit();
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `pto_crp` 
                SET `fecha` = ?, `objeto` = ?, `num_contrato` = ?, `id_manu` = ?, `tesoreria` = ?, `id_tercero_api` = ? 
            WHERE `id_pto_crp` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $fecha, PDO::PARAM_STR);
    $sql->bindParam(2, $objeto, PDO::PARAM_STR);
    $sql->bindParam(3, $num_solicitud, PDO::PARAM_STR);
    $sql->bindParam(4, $id_manu, PDO::PARAM_INT);
    $sql->bindParam(5, $tesoreria, PDO::PARAM_INT);
    $sql->bindParam(6, $id_tercero, PDO::PARAM_INT);
    $sql->bindParam(7, $id_crp, PDO::PARAM_INT);
    if (!($sql->execute())) {
        $response['msg'] = $sql->errorInfo()[2];
        exit();
    } else {
        $primer = $sql->rowCount();
        $segundo = 0;
        $tercer = 0;
        if ($id_tercero != $id_teractual && $id_adq > 0) {
            $sql = "UPDATE `ctt_adquisiciones` SET `id_tercero` = ? WHERE `id_adquisicion` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_tercero, PDO::PARAM_INT);
            $sql->bindParam(2, $id_adq, PDO::PARAM_INT);
            $sql->execute();
            $segundo = $sql->rowCount();
        }
        if ($id_tercero != $id_teractual) {
            $sql = "UPDATE `pto_crp_detalle` SET `id_tercero_api` = ? WHERE `id_pto_crp` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_tercero, PDO::PARAM_INT);
            $sql->bindParam(2, $id_crp, PDO::PARAM_INT);
            $sql->execute();
            $tercer = $sql->rowCount();
        }
        if ($primer > 0 || $segundo > 0 || $tercer > 0) {
            $sql = "UPDATE `pto_crp` SET `id_user_act` = ?, `fecha_act` = ? WHERE `id_pto_crp` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_user, PDO::PARAM_STR);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_crp, PDO::PARAM_INT);
            $sql->execute();
            $response['status'] = 'ok';
        } else {
            $response['msg'] = 'No se registró ningún nuevo dato';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

echo json_encode($response);
