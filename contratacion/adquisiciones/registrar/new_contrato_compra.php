<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_compra = isset($_POST['id_cc']) ? $_POST['id_cc'] : exit('Acción no permitida');
$fec_ini =  date('Y-m-d', strtotime($_POST['datFecIniEjec']));
$fec_fin = date('Y-m-d', strtotime($_POST['datFecFinEjec']));
$val_contrata = $_POST['numValContrata'];
$forma_pago = $_POST['slcFormPago'];
$supervisor = $_POST['slcSupervisor'];
$id_tercero = $_POST['id_tercero'];
$id_secop = $_POST['txtCodSecop'];
$num_contrato = $_POST['txtCodIntern'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `ctt_adquisiciones` SET `id_tercero` = ? WHERE `id_adquisicion` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_tercero, PDO::PARAM_INT);
    $sql->bindParam(2, $id_compra, PDO::PARAM_INT);
    $sql->execute();
    if (!($sql->rowCount() > 0)) {
        echo $sql->errorInfo()[2];
        exit();
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `ctt_contratos`(`id_compra`,`fec_ini`,`fec_fin`, `val_contrato`,`id_forma_pago`,`id_supervisor`,`id_secop`,`num_contrato`,`id_user_reg`,`fec_reg`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_compra, PDO::PARAM_INT);
    $sql->bindParam(2, $fec_ini, PDO::PARAM_STR);
    $sql->bindParam(3, $fec_fin, PDO::PARAM_STR);
    $sql->bindParam(4, $val_contrata, PDO::PARAM_STR);
    $sql->bindParam(5, $forma_pago, PDO::PARAM_INT);
    $sql->bindParam(6, $supervisor, PDO::PARAM_INT);
    $sql->bindParam(7, $id_secop, PDO::PARAM_STR);
    $sql->bindParam(8, $num_contrato, PDO::PARAM_STR);
    $sql->bindParam(9, $id_user, PDO::PARAM_INT);
    $sql->bindValue(10, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    $id_contrato = $cmd->lastInsertId();
    if ($id_contrato > 0) {
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
                $sql->bindParam(1, $id_contrato, PDO::PARAM_INT);
                $sql->bindParam(2, $id_pol, PDO::PARAM_INT);
                $sql->bindParam(3, $id_user, PDO::PARAM_INT);
                $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
                foreach ($polizas as $p) {
                    $id_pol = $p;
                    $sql->execute();
                    if ($cmd->lastInsertId() > 0) {
                        $cant++;
                    } else {
                        echo $sql->errorInfo()[2];
                    }
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
        if ($cant > 0) {
            try {
                $estado = 7;
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "UPDATE `ctt_adquisiciones` SET `estado`= ?, `id_user_act` = ?, `fec_act` = ? WHERE `id_adquisicion` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $estado, PDO::PARAM_INT);
                $sql->bindParam(2, $id_user, PDO::PARAM_INT);
                $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(4, $id_compra, PDO::PARAM_INT);
                $sql->execute();
                if (!($sql->rowCount() > 0)) {
                    echo $sql->errorInfo()[2];
                } else {
                    echo '1';
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        } else {
            echo 'No se registró ninguna póliza';
        }
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
