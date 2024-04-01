<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_funca = isset($_POST['id_funca']) ? $_POST['id_funca'] : exit('Acción no permitida');
$id_serie = $_POST['id_serie'];
$estadoFunca = $_POST['estadoFunca'];
$numAniosOut = $_POST['numAniosOut'];
$causas = $_POST['txtaFuncionamiento'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
if ($id_funca == '0') {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `seg_funcionamiento_acfijo`
                    (`id_serie`, `estado`, `anios_fuera_servicio`, `causas`, `id_user_reg`, `fec_reg`)
                VALUES (?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_serie, PDO::PARAM_INT);
        $sql->bindParam(2, $estadoFunca, PDO::PARAM_STR);
        $sql->bindParam(3, $numAniosOut, PDO::PARAM_INT);
        $sql->bindParam(4, $causas, PDO::PARAM_STR);
        $sql->bindParam(5, $iduser, PDO::PARAM_INT);
        $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            echo '1';
        } else {
            echo $sql->errorInfo()[2];
        }
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE `seg_funcionamiento_acfijo`
                    SET `estado` = ?, `anios_fuera_servicio` = ?, `causas` = ? WHERE `id_funca` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $estadoFunca, PDO::PARAM_INT);
        $sql->bindParam(2, $numAniosOut, PDO::PARAM_INT);
        $sql->bindParam(3, $causas, PDO::PARAM_STR);
        $sql->bindParam(4, $id_funca, PDO::PARAM_INT);
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "UPDATE `seg_funcionamiento_acfijo` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_funca` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $id_funca, PDO::PARAM_INT);
                $sql->execute();
                if ($sql->rowCount() > 0) {
                    echo '1';
                } else {
                    echo $sql->errorInfo()[2];
                    exit();
                }
            } else {
                echo 'No se ingresó ningún dato nuevo';
            }
        }
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
