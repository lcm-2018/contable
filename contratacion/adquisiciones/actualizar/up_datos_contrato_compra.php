<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_cc = isset($_POST['id_cc']) ? $_POST['id_cc'] : exit('Acción no permitida');
$fec_ini =  date('Y-m-d', strtotime($_POST['datFecIniEjec']));
$fec_fin = date('Y-m-d', strtotime($_POST['datFecFinEjec']));
$forma_pago = $_POST['slcFormPago'];
$supervisor = $_POST['slcSupervisor'];
$id_tercero = $_POST['id_tercero'];
$id_compra = $_POST['id_compra'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$change = 0;

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $sql = "UPDATE `ctt_adquisiciones` SET `id_tercero` = ? WHERE `id_adquisicion` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_tercero, PDO::PARAM_INT);
    $sql->bindParam(2, $id_compra, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        $change = 1;
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql1 = "UPDATE `ctt_contratos` 
            SET `fec_ini` = ?, `fec_fin` = ?, `id_forma_pago` = ?, `id_supervisor` = ? 
            WHERE `id_contrato_compra` = ?";
    $sql1 = $cmd->prepare($sql1);
    $sql1->bindParam(1, $fec_ini, PDO::PARAM_STR);
    $sql1->bindParam(2, $fec_fin, PDO::PARAM_STR);
    $sql1->bindParam(3, $forma_pago, PDO::PARAM_INT);
    $sql1->bindParam(4, $supervisor, PDO::PARAM_INT);
    $sql1->bindParam(5, $id_cc, PDO::PARAM_INT);
    if (!($sql1->execute())) {
        echo $sql1->errorInfo()[2];
        exit();
    } else {
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $query = "DELETE FROM `ctt_garantias_compra` WHERE `id_contrato_compra` = '$id_cc'";
            $query = $cmd->prepare($query);
            $query->bindParam(1, $id_cc, PDO::PARAM_INT);
            $query->execute();
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        $polizas = isset($_POST['check']) ? $_POST['check'] : '';
        $cant = 0;
        $cambio = 0;
        if ($polizas == '') {
            $cant = 1;
        } else {
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
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
                        echo $sql->errorInfo()[2];
                    }
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
        if ($sql1->rowCount() > 0 || $cant > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "UPDATE  `ctt_contratos` SET  `id_user_act` = ? , `fec_act` = ? WHERE `id_contrato_compra` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_cc, PDO::PARAM_INT);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                $change = 1;
            } else {
                echo $sql->errorInfo()[2];
            }
        }
    }
    if ($change == 1) {
        echo '1';
    } else {
        echo 'No se ha realizado ningun cambio';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
