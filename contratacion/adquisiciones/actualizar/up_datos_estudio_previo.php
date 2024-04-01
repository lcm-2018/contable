<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_est_prev = isset($_POST['id_est_prev']) ? $_POST['id_est_prev'] : exit('Acción no permitida');
$fec_ini =  date('Y-m-d', strtotime($_POST['datFecIniEjec']));
$fec_fin = date('Y-m-d', strtotime($_POST['datFecFinEjec']));
$val_contrata = $_POST['numValContrata'];
$forma_pago = $_POST['slcFormPago'];
$supervisor = $_POST['slcSupervisor'];
$DescNec = $_POST['necesidad'];
$ActEspecificas = $_POST['actividades'];
$ProdEntrega = $_POST['productos'];
$ObligContratista = $_POST['obligaciones'];
$FormPago = $_POST['pago'];
$numDS = $_POST['numDS'];
$requisitos = $_POST['reqMinHab'];
$garantia = $_POST['garant'];
$describe_valor = $_POST['descVal'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `ctt_estudios_previos` SET `fec_ini_ejec` = ?, `fec_fin_ejec` = ?, `val_contrata` = ?, `id_forma_pago` = ?, `id_supervisor` = ?
                    ,`necesidad` = ? ,`act_especificas` = ? ,`prod_entrega` = ? ,`obligaciones` = ? ,`forma_pago` = ?, `num_ds` = ?, `requisitos` = ?, `garantia` = ?, `describe_valor` = ?
                    WHERE `id_est_prev` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $fec_ini, PDO::PARAM_STR);
    $sql->bindParam(2, $fec_fin, PDO::PARAM_STR);
    $sql->bindParam(3, $val_contrata, PDO::PARAM_STR);
    $sql->bindParam(4, $forma_pago, PDO::PARAM_INT);
    $sql->bindParam(5, $supervisor, PDO::PARAM_INT);
    $sql->bindParam(6, $DescNec, PDO::PARAM_STR);
    $sql->bindParam(7, $ActEspecificas, PDO::PARAM_STR);
    $sql->bindParam(8, $ProdEntrega, PDO::PARAM_STR);
    $sql->bindParam(9, $ObligContratista, PDO::PARAM_STR);
    $sql->bindParam(10, $FormPago, PDO::PARAM_STR);
    $sql->bindParam(11, $numDS, PDO::PARAM_STR);
    $sql->bindParam(12, $requisitos, PDO::PARAM_STR);
    $sql->bindParam(13, $garantia, PDO::PARAM_STR);
    $sql->bindParam(14, $describe_valor, PDO::PARAM_STR);
    $sql->bindParam(15, $id_est_prev, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        $cambio = $sql->rowCount();
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "DELETE FROM `seg_garantias_compra` WHERE `id_est_prev` = '$id_est_prev'";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_est_prev, PDO::PARAM_INT);
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
                $sql = "INSERT INTO `seg_garantias_compra`(`id_est_prev`,`id_poliza`,`id_user_reg`,`fec_reg`) VALUES (?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id_est_prev, PDO::PARAM_INT);
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
        if ($cambio > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE  `ctt_estudios_previos` SET  `id_user_act` = ? , `fec_act` = ? WHERE `id_est_prev` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_est_prev, PDO::PARAM_INT);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo '1';
            } else {
                echo $sql->errorInfo()[2];
            }
        } else {
            echo 'No se ha modificado ningún dato';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
