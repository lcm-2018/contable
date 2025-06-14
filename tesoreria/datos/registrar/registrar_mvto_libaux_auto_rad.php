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
$id_crp = $_post['id_crp'];
$id_cop = $_post['id_cop'];
$tipo = $_post['tipo'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
$response['status'] = 'error';
$registros = 0;

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $query = "DELETE FROM `ctb_libaux` WHERE `id_ctb_doc` = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_doc, PDO::PARAM_INT);
    $query->execute();
    $query = "SELECT 
                `ctb_doc`.`id_tercero`, `ctb_referencia`.`id_cuenta`
            FROM `ctb_doc`
            INNER JOIN `ctb_referencia` 
                ON (`ctb_doc`.`id_ref_ctb` = `ctb_referencia`.`id_ctb_referencia`)
            WHERE `ctb_doc`.`id_ctb_doc` = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_doc, PDO::PARAM_INT);
    $query->execute();
    $datos = $query->fetch();
    $id_tercero = $datos['id_tercero'];
    $id_cta_credito = $datos['id_cuenta'];
    $sq2 = "SELECT
                `tes_cuentas`.`id_cuenta` AS `cta_contable`
                , `tes_detalle_pago`.`valor`
            FROM
                `tes_detalle_pago`
                INNER JOIN `tes_cuentas` 
                    ON (`tes_detalle_pago`.`id_tes_cuenta` = `tes_cuentas`.`id_tes_cuenta`)
            WHERE (`tes_detalle_pago`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sq2);
    $formapago = $rs->fetch();

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
    $id_cuenta =  $formapago['cta_contable'];
    $debito = $formapago['valor'];
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        $debito = 0;
        $id_cuenta = $id_cta_credito;
        $credito = $formapago['valor'];
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $registros++;
        } else {
            $response['msg'] += $sql->errorInfo()[2];
        }
    } else {
        $response['msg'] += $sql->errorInfo()[2];
    }
} catch (PDOException $e) {
    $response['msg'] =  $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if ($registros > 0) {
    $response['status'] = 'ok';
}
echo json_encode($response);
