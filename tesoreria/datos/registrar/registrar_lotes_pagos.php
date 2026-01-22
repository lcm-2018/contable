<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../../../conexion.php';
include '../../../financiero/consultas.php';
$ids = isset($_POST['check']) ? $_POST['check'] : exit('Accion no permitida');
$ids_cops = implode(',', $ids);
$id_tipo = $_POST['id_tipo'];
$estado = 1;

$iduser = $_SESSION['id_user'];
$id_vigencia = $_SESSION['id_vigencia'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');

$response['status'] = 'error';
$response['msg'] = '';
$registros = 0;

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 56, $cmd);
try {
    $sql = "SELECT MAX(`id_manu`) AS `id_manu` FROM `ctb_doc` 
            WHERE (`id_tipo_doc` = $id_tipo AND `id_vigencia` = $id_vigencia)";
    $rs = $cmd->query($sql);
    $id_manu = $rs->fetch(PDO::FETCH_ASSOC);
    $id_manu = !empty($id_manu['id_manu']) ? $id_manu['id_manu'] : 1;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                    `ctb_doc`.`id_ctb_doc`
                    ,`ctb_doc`.`id_tercero`
                    , (SELECT `fecha` FROM `tes_referencia` WHERE `id_referencia` = (SELECT MAX(`id_referencia`) AS `id_referencia` FROM `tes_referencia` WHERE (`estado` = 1))) AS `fecha`
                    , `ctb_doc`.`detalle`
                    , (SELECT MAX(`id_referencia`) AS `id_referencia` FROM `tes_referencia` WHERE (`estado` = 1)) AS `id_ref`
                    , `tb_terceros`.`nom_tercero`
                    , `causado`.`ids_det`
                    , `causado`.`valores`
                    , (SELECT `id_tes_cuenta` AS `cta_contable` FROM `tes_referencia` WHERE `id_referencia` = (SELECT MAX(`id_referencia`) AS `id_referencia` FROM `tes_referencia` WHERE (`estado` = 1))) AS `banco`
                    , (SELECT `id_cuenta` AS `cta_contable` FROM `tes_cuentas` WHERE (`est_nomina` = 1)) AS `cta_contable`
                    , 1 AS `forma_pago`
                    , `libros`.`id_cuenta` AS `id_cuenta`
                    , `libros`.`valor` AS `valor_referencia`
                FROM `ctb_doc`
                    INNER JOIN `ctb_fuente`
		                ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                    LEFT JOIN `tb_terceros`
                        ON(`ctb_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                    LEFT JOIN
                        (SELECT 
                            `id_ctb_doc`
                            , GROUP_CONCAT(`id_pto_cop_det` ORDER BY `id_pto_cop_det` SEPARATOR ',') AS `ids_det`
                            , GROUP_CONCAT(`valor` ORDER BY `id_pto_cop_det` SEPARATOR ',') AS `valores`
                        FROM
                            (SELECT
                                `id_ctb_doc`
                                , `id_pto_cop_det`
                                , SUM(IFNULL(`valor`,0) - IFNULL(`valor_liberado`,0)) AS `valor`
                            FROM
                                `pto_cop_detalle`
                            GROUP BY `id_ctb_doc`, `id_pto_crp_det`, `id_pto_cop_det`) AS `tt`
                        GROUP BY `id_ctb_doc`) AS `causado`
                        ON (`ctb_doc`.`id_ctb_doc` = `causado`.`id_ctb_doc`)
                    LEFT JOIN 
                        (SELECT
                            `id_ctb_doc`, `id_cuenta`, `credito` AS `valor`
                        FROM
                            `ctb_libaux`
                        WHERE (`credito` > 0 AND `ref` = 1)) AS `libros`
                        ON (`ctb_doc`.`id_ctb_doc` = `libros`.`id_ctb_doc`)
                WHERE (`ctb_doc`.`id_ctb_doc` IN ($ids_cops))";
    $rs = $cmd->query($sql);
    $causaciones = $rs->fetchAll(PDO::FETCH_ASSOC);
    //exit(json_encode($causaciones));
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $cmd->beginTransaction();

    foreach ($causaciones as $cs) {
        //Insertar en `ctb_doc`
        $id_manu = $id_manu + 1;

        $fec_doc = $cs['fecha'] > $fecha_cierre ? $cs['fecha'] : date('Y-m-d', strtotime($fecha_cierre . ' +1 day'));
        $query = "INSERT INTO `ctb_doc`
                        (`id_vigencia`,`id_tipo_doc`,`id_manu`,`id_tercero`,`fecha`,`detalle`,`estado`,`id_user_reg`,`fecha_reg`,`id_ref`,`id_ctb_doc_tipo3`)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $cmd->prepare($query);
        $stmt->bindParam(1, $id_vigencia, PDO::PARAM_INT);
        $stmt->bindParam(2, $id_tipo, PDO::PARAM_INT);
        $stmt->bindParam(3, $id_manu, PDO::PARAM_INT);
        $stmt->bindParam(4, $cs['id_tercero'], PDO::PARAM_INT);
        $stmt->bindParam(5, $fec_doc, PDO::PARAM_STR);
        $stmt->bindParam(6, $cs['detalle'], PDO::PARAM_STR);
        $stmt->bindParam(7, $estado, PDO::PARAM_INT);
        $stmt->bindParam(8, $iduser, PDO::PARAM_INT);
        $stmt->bindParam(9, $fecha2);
        $stmt->bindParam(10, $cs['id_ref'], PDO::PARAM_INT);
        $stmt->bindParam(11, $cs['id_ctb_doc'], PDO::PARAM_INT);
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar en `ctb_doc`: " . implode(" | ", $stmt->errorInfo()));
        }
        // Insertar en `tes_rel_pag_cop`
        $id_pag = $cmd->lastInsertId();
        $sql = "INSERT INTO `tes_rel_pag_cop` (`id_doc_cop`,`id_doc_pag`) VALUES (?, ?)";
        $stmt2 = $cmd->prepare($sql);
        $stmt2->bindParam(1, $cs['id_ctb_doc'], PDO::PARAM_INT);
        $stmt2->bindParam(2, $id_pag, PDO::PARAM_INT);
        if (!$stmt2->execute()) {
            throw new Exception("Error al insertar en `tes_rel_pag_cop`: " . implode(" | ", $stmt2->errorInfo()));
        }
        // Insertar en `pto_pag_detalle`
        $ids_det = explode(',', $cs['ids_det']);
        $valores = explode(',', $cs['valores']);
        $liberado = 0;
        $pagar = 0;
        foreach ($ids_det as $key => $id_det) {
            if (!empty($id_det)) {
                $pagar += $valores[$key];
                $sql2 = "INSERT INTO `pto_pag_detalle`
                            (`id_ctb_doc`,`id_pto_cop_det`,`valor`,`valor_liberado`,`id_tercero_api`,`id_user_reg`,`fecha_reg`)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt3 = $cmd->prepare($sql2);
                $stmt3->bindParam(1, $id_pag, PDO::PARAM_INT);
                $stmt3->bindParam(2, $id_det, PDO::PARAM_INT);
                $stmt3->bindParam(3, $valores[$key], PDO::PARAM_STR);
                $stmt3->bindParam(4, $liberado, PDO::PARAM_STR);
                $stmt3->bindParam(5, $cs['id_tercero'], PDO::PARAM_INT);
                $stmt3->bindParam(6, $iduser, PDO::PARAM_INT);
                $stmt3->bindParam(7, $fecha2);
                if (!$stmt3->execute()) {
                    throw new Exception("Error al insertar en `pto_pag_detalle`: " . implode(" | ", $stmt3->errorInfo()));
                }
            }
        }
        // Insertar forma de pago
        $documento = 0;
        $sql3 = "INSERT INTO `tes_detalle_pago`
                    (`id_ctb_doc`,`id_tes_cuenta`,`id_forma_pago`,`documento`,`valor`,`id_user_reg`,`fecha_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt4 = $cmd->prepare($sql3);
        $stmt4->bindParam(1, $id_pag, PDO::PARAM_INT);
        $stmt4->bindParam(2, $cs['banco'], PDO::PARAM_INT);
        $stmt4->bindParam(3, $cs['forma_pago'], PDO::PARAM_INT);
        $stmt4->bindParam(4, $documento, PDO::PARAM_STR);
        $stmt4->bindParam(5, $cs['valor_referencia'], PDO::PARAM_STR);
        $stmt4->bindParam(6, $iduser, PDO::PARAM_INT);
        $stmt4->bindParam(7, $fecha2);
        if (!$stmt4->execute()) {
            throw new Exception("Error al insertar en `tes_detalle_pago`: " . implode(" | ", $stmt4->errorInfo()));
        }
        //Insertar en `ctb_libaux`
        $debito = 0;
        $credito = $cs['valor_referencia'];
        $cuenta = $cs['cta_contable'];
        $sql4 = "INSERT INTO `ctb_libaux`
                (`id_ctb_doc`,`id_tercero_api`,`id_cuenta`,`debito`,`credito`,`id_user_reg`,`fecha_reg`)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt5 = $cmd->prepare($sql4);
        $stmt5->bindParam(1, $id_pag, PDO::PARAM_INT);
        $stmt5->bindParam(2, $cs['id_tercero'], PDO::PARAM_INT);
        $stmt5->bindParam(3, $cuenta, PDO::PARAM_INT);
        $stmt5->bindParam(4, $debito, PDO::PARAM_STR);
        $stmt5->bindParam(5, $credito, PDO::PARAM_STR);
        $stmt5->bindParam(6, $iduser, PDO::PARAM_INT);
        $stmt5->bindParam(7, $fecha2);
        if (!$stmt5->execute()) {
            throw new Exception("Error al insertar en `ctb_libaux`: " . implode(" | ", $stmt5->errorInfo()));
        }
        $credito = 0;
        $debito = $cs['valor_referencia'];
        $cuenta = $cs['id_cuenta'];
        if (!$stmt5->execute()) {
            throw new Exception("Error al insertar en `ctb_libaux`: " . implode(" | ", $stmt5->errorInfo()));
        }
    }
    $cmd->commit();
    $response['status'] = 'ok';
} catch (Exception $e) {
    if ($cmd->inTransaction()) {
        $cmd->rollBack();
    }
    $response['msg'] = "Error en la transacción: " . $e->getMessage();
} finally {
    $cmd = null;
}

echo json_encode($response);
