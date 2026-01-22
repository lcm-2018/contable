<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_pto = $_POST['id_pto_presupuestos'];
$id_manu = $_POST['numCdp'];
$fecha = $_POST['fecha'];
$contrato = $_POST['contrato'];
$tercero = $_POST['id_tercero'];
$objeto = $_POST['objeto'];
$detalles = $_POST['detalle'];
$id_crp = $_POST['id_crp'];
$tesoreria = isset($_POST['chDestTes']) ? 1 : 0;
$id_cdp = $_POST['id_cdp'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$estado = 1;
$id_vigencia = $_SESSION['id_vigencia'];
$response['status'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_manu` 
            FROM
                `pto_crp`
            WHERE (`id_pto` = $id_pto AND `id_manu` = $id_manu)";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    if (!empty($consecutivo)) {
        $response['msg'] = 'El consecutivo de CDP <b>' . $id_manu . '</b> ya se encuentra registrado';
        echo json_encode($response);
        exit();
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
        $cmd = null;
    } catch (PDOException $e) {
        $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
if ($id_crp == 0) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `pto_crp`
                (`id_pto`, `id_cdp`,`fecha`,`id_manu`,`id_tercero_api`,`objeto`,`num_contrato`,`estado`,`id_user_reg`,`fecha_reg`,`tesoreria`)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_pto, PDO::PARAM_INT);
        $sql->bindParam(2, $id_cdp, PDO::PARAM_INT);
        $sql->bindParam(3, $fecha, PDO::PARAM_STR);
        $sql->bindParam(4, $id_manu, PDO::PARAM_INT);
        $sql->bindParam(5, $tercero, PDO::PARAM_INT);
        $sql->bindParam(6, $objeto, PDO::PARAM_STR);
        $sql->bindParam(7, $contrato, PDO::PARAM_STR);
        $sql->bindParam(8, $estado, PDO::PARAM_STR);
        $sql->bindParam(9, $id_user, PDO::PARAM_INT);
        $sql->bindValue(10, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(11, $tesoreria, PDO::PARAM_INT);
        $sql->execute();
        $id_new = $cmd->lastInsertId();
        $cmd = null;
        if ($id_new > 0) {
            $cerrado = 2;
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            if ($tesoreria == 1) {
                $sql2 = "INSERT INTO `ctb_doc`
                    (`id_vigencia`,`id_tipo_doc`,`id_manu`,`id_tercero`,`fecha`,`detalle`,`estado`,`id_user_reg`,`fecha_reg`,`id_crp`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sql2 = $cmd->prepare($sql2);
                $sql2->bindParam(1, $id_vigencia, PDO::PARAM_INT);
                $sql2->bindParam(2, $id_doc_fuente, PDO::PARAM_INT);
                $sql2->bindParam(3, $id_consec, PDO::PARAM_INT);
                $sql2->bindParam(4, $tercero, PDO::PARAM_INT);
                $sql2->bindParam(5, $fecha, PDO::PARAM_STR);
                $sql2->bindParam(6, $objeto, PDO::PARAM_STR);
                $sql2->bindParam(7, $cerrado, PDO::PARAM_INT);
                $sql2->bindParam(8, $id_user, PDO::PARAM_INT);
                $sql2->bindParam(9, $fecha2);
                $sql2->bindParam(10, $id_new, PDO::PARAM_INT);
                $sql2->execute();
                $id_doc = $cmd->lastInsertId();
                if (!($id_doc > 0)) {
                    $response['msg'] = $sql2->errorInfo()[2];
                    echo json_encode($response);
                    exit();
                }
            }
            $query = "INSERT INTO `pto_crp_detalle`
                        (`id_pto_crp`,`id_pto_cdp_det`,`id_tercero_api`,`valor`,`id_user_reg`,`fecha_reg`)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $query = $cmd->prepare($query);
            $query->bindParam(1, $id_new, PDO::PARAM_INT);
            $query->bindParam(2, $id_detalle, PDO::PARAM_INT);
            $query->bindParam(3, $tercero, PDO::PARAM_INT);
            $query->bindParam(4, $valor, PDO::PARAM_STR);
            $query->bindParam(5, $id_user, PDO::PARAM_INT);
            $query->bindValue(6, $date->format('Y-m-d H:i:s'));
            foreach ($detalles as $key => $value) {
                $id_detalle = $key;
                $valor = str_replace(',', '', $value);
                $query->execute();
                $id_crp_det = $cmd->lastInsertId();
                if (!($id_crp_det > 0)) {
                    $response['msg'] = $query->errorInfo()[2];
                    break;
                } else {
                    if ($tesoreria == 1) {
                        $liberado = 0;
                        $query2 = "INSERT INTO `pto_cop_detalle`
                                (`id_ctb_doc`, `id_pto_crp_det`, `id_tercero_api`, `valor`, `valor_liberado`, `id_user_reg`, `fecha_reg`)
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $query2 = $cmd->prepare($query2);
                        $query2->bindParam(1, $id_doc, PDO::PARAM_INT);
                        $query2->bindParam(2, $id_crp_det, PDO::PARAM_INT);
                        $query2->bindParam(3, $tercero, PDO::PARAM_INT);
                        $query2->bindParam(4, $valor, PDO::PARAM_STR);
                        $query2->bindParam(5, $liberado, PDO::PARAM_STR);
                        $query2->bindParam(6, $id_user, PDO::PARAM_INT);
                        $query2->bindParam(7, $fecha2, PDO::PARAM_STR);
                        $query2->execute();
                        if (!($query2->rowCount() > 0)) {
                            $response['msg'] = $query2->errorInfo()[2];
                            break;
                        }
                    }
                }
            }
            $response['status'] = 'ok';
            $response['msg'] = $id_new;
        } else {
            $response['msg'] = $sql->errorInfo()[2];
        }
        $cmd = null;
    } catch (PDOException $e) {
        $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
} else {
}

echo json_encode($response);
