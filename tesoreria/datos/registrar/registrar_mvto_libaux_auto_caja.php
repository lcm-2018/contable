<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
//Recibir variables por POST
include '../../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);

$id_doc = $_post['id'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
$response['status'] = 'error';
$registros = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tes_caja_mvto`.`valor`
                , `tes_caja_rubros`.`id_cta_contable`
            FROM
                `tes_caja_mvto`
                INNER JOIN `tes_caja_rubros` 
                    ON (`tes_caja_mvto`.`id_caja_rubros` = `tes_caja_rubros`.`id_caja_rubros`)
            WHERE (`tes_caja_mvto`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $cuentas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] =  $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tes_detalle_pago`.`id_ctb_doc`
                , `tes_cuentas`.`id_cuenta`
                , `tes_detalle_pago`.`valor`
            FROM
                `tes_detalle_pago`
                INNER JOIN `tes_cuentas` 
                    ON (`tes_detalle_pago`.`id_tes_cuenta` = `tes_cuentas`.`id_tes_cuenta`)
            WHERE (`tes_detalle_pago`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $pago = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] =  $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_tercero` FROM `ctb_doc` WHERE `id_ctb_doc` = $id_doc";
    $rs = $cmd->query($sql);
    $id_tercero = $rs->fetch(PDO::FETCH_ASSOC)['id_tercero'];
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] =  $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $query = "DELETE FROM `ctb_libaux` WHERE `id_ctb_doc` = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_doc);
    $query->execute();
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] =  $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "INSERT INTO `ctb_libaux`
                (`id_ctb_doc`,`id_tercero_api`,`id_cuenta`,`debito`,`credito`,`id_user_reg`,`fecha_reg`)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_doc, PDO::PARAM_INT);
    $sql->bindParam(2, $id_tercero, PDO::PARAM_INT);
    $sql->bindParam(3, $id_cuenta, PDO::PARAM_INT);
    $sql->bindParam(4, $debito, PDO::PARAM_STR);
    $sql->bindParam(5, $credito, PDO::PARAM_STR);
    $sql->bindParam(6, $iduser, PDO::PARAM_INT);
    $sql->bindParam(7, $fecha2);
    $credito = 0;
    foreach ($cuentas as $c) {
        $id_cuenta = $c['id_cta_contable'];
        $debito = $c['valor'];
        $sql->execute();
        if ($sql->rowCount() > 0) {
            $registros++;
        } else {
            echo $sql->errorInfo()[2];
        }
    }
    $debito = 0;
    foreach ($pago as $p) {
        $id_cuenta = $p['id_cuenta'];
        $credito = $p['valor'];
        $sql->execute();
        if ($sql->rowCount() > 0) {
            $registros++;
        } else {
            echo $sql->errorInfo()[2];
        }
    }
} catch (PDOException $e) {
    $response['msg'] =  $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if ($registros > 0) {
    $response['status'] = 'ok';
    $response['msg'] = 'Se han registrado los movimientos contables';
}
echo json_encode($response);
