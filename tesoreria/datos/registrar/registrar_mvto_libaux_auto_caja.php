<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
//Recibir variables por POST
include '../../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);
print_r($_post);
exit();
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
    $query = "DELETE FROM `tes_caja_mvto` WHERE `id_ctb_doc` = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_doc, PDO::PARAM_INT);
    $query->execute();
    $query = "SELECT `id_tercero` FROM `ctb_doc` WHERE `id_ctb_doc` = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_doc, PDO::PARAM_INT);
    $query->execute();
    $datos = $query->fetch();
    $id_tercero = $datos['id_tercero'];
    $sq2 = "SELECT
                `tes_cuentas`.`id_cuenta` AS `cta_contable`
                , `tes_detalle_pago`.`valor`
            FROM
                `tes_detalle_pago`
                INNER JOIN `tes_cuentas` 
                    ON (`tes_detalle_pago`.`id_tes_cuenta` = `tes_cuentas`.`id_tes_cuenta`)
            WHERE (`tes_detalle_pago`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sq2);
    $formapago = $rs->fetchAll();
    if ($tipo != 4) {
        $sql = "SELECT
                `ctb_referencia`.`id_cuenta` AS `cuenta`
                , `ctb_referencia`.`accion`
            FROM
                `ctb_doc`
                INNER JOIN `ctb_referencia` 
                    ON (`ctb_doc`.`id_ref_ctb` = `ctb_referencia`.`id_ctb_referencia`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $id_doc)";
        $rs = $cmd->query($sql);
        $cuenta_ctb = $rs->fetch();
    } else {
        $sql = "SELECT
                    `pto_pag_detalle`.`id_ctb_doc`
                    , `pto_cop_detalle`.`id_pto_cop_det`
                    , `ctb_libaux`.`id_cuenta`
                    , `pto_pag_detalle`.`valor`
                FROM
                    `pto_pag_detalle`
                    INNER JOIN `pto_cop_detalle` 
                        ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                    INNER JOIN `ctb_libaux` 
                        ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_libaux`.`id_ctb_doc`)
                WHERE (`pto_pag_detalle`.`id_ctb_doc` = $id_doc
                    AND `ctb_libaux`.`debito` > 0)";
        $rs = $cmd->query($sql);
        $cuenta_ctb = $rs->fetchAll();
    }
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
    $debito = 0;
    $total = 0;
    foreach ($formapago as $fp) {
        $id_cuenta = $fp['cta_contable'];
        $credito = $fp['valor'];
        $total += $credito;
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $registros++;
        } else {
            $response['msg'] += $sql->errorInfo()[2];
        }
    }
    $credito = 0;
    if ($tipo != 4) {
        if (empty($cuenta_ctb)) {
            $id_cuenta = NULL;
        } else {
            $id_cuenta = $cuenta_ctb['cuenta'];
        }
        $debito = $total;
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $registros++;
        } else {
            $response['msg'] += $sql->errorInfo()[2];
        }
    } else {
        foreach ($cuenta_ctb as $cc) {
            $id_cuenta = $cc['id_cuenta'];
            $debito = $cc['valor'];
            $sql->execute();
            if ($cmd->lastInsertId() > 0) {
                $registros++;
            } else {
                $response['msg'] += $sql->errorInfo()[2];
            }
        }
    }
} catch (PDOException $e) {
    $response['msg'] =  $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if ($registros > 0) {
    $response['status'] = 'ok';
}
echo json_encode($response);
