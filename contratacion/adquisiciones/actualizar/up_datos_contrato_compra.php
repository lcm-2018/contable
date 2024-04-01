<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_cc = isset($_POST['id_cc']) ? $_POST['id_cc'] : exit('Acción no permitida');
$fec_ini =  date('Y-m-d', strtotime($_POST['datFecIniEjec']));
$fec_fin = date('Y-m-d', strtotime($_POST['datFecFinEjec']));
$forma_pago = $_POST['slcFormPago'];
$supervisor = $_POST['slcSupervisor'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `ctt_contratos` SET `fec_ini` = ?, `fec_fin` = ?, `id_forma_pago` = ?, `id_supervisor` = ? WHERE `id_contrato_compra` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $fec_ini, PDO::PARAM_STR);
    $sql->bindParam(2, $fec_fin, PDO::PARAM_STR);
    $sql->bindParam(3, $forma_pago, PDO::PARAM_INT);
    $sql->bindParam(4, $supervisor, PDO::PARAM_INT);
    $sql->bindParam(5, $id_cc, PDO::PARAM_INT);
    $sql->execute();
    $cambio = $sql->rowCount();
    if (!($sql->execute())) {
        print_r($sql->errorInfo()[2]);
        exit();
    } else {
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "DELETE FROM `ctt_garantias_compra` WHERE `id_contrato_compra` = '$id_cc'";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_cc, PDO::PARAM_INT);
            $sql->execute();
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        $polizas = isset($_REQUEST['check']) ? $_REQUEST['check'] : '';
        $cant = 0;
        if ($polizas == '') {
            $cant = 1;
        } else {
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO `ctt_garantias_compra`(`id_contrato_compra`,`id_poliza`,`id_user_reg`,`fec_reg`) VALUES (?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id_cc, PDO::PARAM_INT);
                $sql->bindParam(2, $id_pol, PDO::PARAM_INT);
                $sql->bindParam(3, $iduser, PDO::PARAM_INT);
                $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
                foreach ($polizas as $p) {
                    $id_pol = $p;
                    $sql->execute();
                    if ($cmd->lastInsertId() > 0) {
                        $cant++;
                        $cambio = 1;
                    } else {
                        print_r($sql->errorInfo()[2]);
                    }
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
        if ($cambio > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE  `ctt_contratos` SET  `id_user_act` = ? , `fec_act` = ? WHERE `id_contrato_compra` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_cc, PDO::PARAM_INT);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo '1';
            } else {
                print_r($sql->errorInfo()[2]);
            }
        } else {
            echo 'No se ha modificado ningún dato';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
