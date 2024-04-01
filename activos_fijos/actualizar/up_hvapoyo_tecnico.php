<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_apoyo = isset($_POST['id_apoyo']) ? $_POST['id_apoyo'] : exit('Acción no permitida');
$id_serie = $_POST['id_serie'];
$riesgo = isset($_POST['riesgoApoyoTec']) ? $_POST['riesgoApoyoTec'] : exit('Debe marcar un nivel de riesgo');
$uso = isset($_POST['usoApoyoTec']) ? $_POST['usoApoyoTec'] : exit('Debe marcar el uso del equipo');
$diagnostico = $_POST['txtDiag'];
$prevencion = $_POST['txtPrev'];
$rehabilitacion = $_POST['txtRehab'];
$analis_lab = $_POST['txtLab'];
$tratamiento = $_POST['txttmnto'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
if ($id_apoyo == '0') {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `seg_hv_apoyo_tecnico`
                    (`id_serie`, `riesgo`, `uso`, `diagnostico`, `prevencion`, `rehabilitacion`, `analis_lab`, `tratamiento`, `id_user_reg`, `fec_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_serie, PDO::PARAM_INT);
        $sql->bindParam(2, $riesgo, PDO::PARAM_INT);
        $sql->bindParam(3, $uso, PDO::PARAM_INT);
        $sql->bindParam(4, $diagnostico, PDO::PARAM_STR);
        $sql->bindParam(5, $prevencion, PDO::PARAM_STR);
        $sql->bindParam(6, $rehabilitacion, PDO::PARAM_STR);
        $sql->bindParam(7, $analis_lab, PDO::PARAM_STR);
        $sql->bindParam(8, $tratamiento, PDO::PARAM_STR);
        $sql->bindParam(9, $iduser, PDO::PARAM_INT);
        $sql->bindValue(10, $date->format('Y-m-d H:i:s'));
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
        $sql = "UPDATE `seg_hv_apoyo_tecnico`
                    SET `riesgo` = ?, `uso` = ?, `diagnostico` = ?, `prevencion` = ?, `rehabilitacion` = ?, `analis_lab` = ?, `tratamiento` = ? WHERE `id_apoyo` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $riesgo, PDO::PARAM_INT);
        $sql->bindParam(2, $uso, PDO::PARAM_INT);
        $sql->bindParam(3, $diagnostico, PDO::PARAM_STR);
        $sql->bindParam(4, $prevencion, PDO::PARAM_STR);
        $sql->bindParam(5, $rehabilitacion, PDO::PARAM_STR);
        $sql->bindParam(6, $analis_lab, PDO::PARAM_STR);
        $sql->bindParam(7, $tratamiento, PDO::PARAM_STR);
        $sql->bindParam(8, $id_apoyo, PDO::PARAM_INT);
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "UPDATE `seg_hv_apoyo_tecnico` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_apoyo` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $id_apoyo, PDO::PARAM_INT);
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
