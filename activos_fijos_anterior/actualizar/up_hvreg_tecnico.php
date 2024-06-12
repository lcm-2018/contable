<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_rango = isset($_POST['id_hv_reg_tec']) ? $_POST['id_hv_reg_tec'] : exit('Acción no permitida');
$id_serie = $_POST['id_serie'];
$v_min = $_POST['vMin'];
$v_max = $_POST['vMax'];
$hz_min = $_POST['hzMin'];
$hz_max = $_POST['hzMax'];
$w_min = $_POST['wMin'];
$w_max = $_POST['wMax'];
$ma_min = $_POST['mAMin'];
$ma_max = $_POST['mAMax'];
$gc_min = $_POST['gCMin'];
$gc_max = $_POST['gCMax'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
if ($id_rango == '0') {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `seg_reg_tecnico_fmto`
                    (`id_serie`, `v_max`, `v_min`, `hz_min`, `hz_max`, `w_min`, `w_max`, `ma_min`, `ma_max`, `gc_min`, `gc_max`, `id_user_reg`, `fec_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_serie, PDO::PARAM_INT);
        $sql->bindParam(2, $v_max, PDO::PARAM_STR);
        $sql->bindParam(3, $v_min, PDO::PARAM_STR);
        $sql->bindParam(4, $hz_min, PDO::PARAM_STR);
        $sql->bindParam(5, $hz_max, PDO::PARAM_STR);
        $sql->bindParam(6, $w_min, PDO::PARAM_STR);
        $sql->bindParam(7, $w_max, PDO::PARAM_STR);
        $sql->bindParam(8, $ma_min, PDO::PARAM_STR);
        $sql->bindParam(9, $ma_max, PDO::PARAM_STR);
        $sql->bindParam(10, $gc_min, PDO::PARAM_STR);
        $sql->bindParam(11, $gc_max, PDO::PARAM_STR);
        $sql->bindParam(12, $iduser, PDO::PARAM_INT);
        $sql->bindValue(13, $date->format('Y-m-d H:i:s'));
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
        $sql = "UPDATE `seg_reg_tecnico_fmto`
                    SET `v_max` = ?, `v_min` = ?, `hz_min` = ?, `hz_max` = ?, `w_min` = ?, `w_max` = ?, `ma_min` = ?, `ma_max` = ?, `gc_min` = ?, `gc_max` = ? WHERE `id_fmto` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $v_max, PDO::PARAM_STR);
        $sql->bindParam(2, $v_min, PDO::PARAM_STR);
        $sql->bindParam(3, $hz_min, PDO::PARAM_STR);
        $sql->bindParam(4, $hz_max, PDO::PARAM_STR);
        $sql->bindParam(5, $w_min, PDO::PARAM_STR);
        $sql->bindParam(6, $w_max, PDO::PARAM_STR);
        $sql->bindParam(7, $ma_min, PDO::PARAM_STR);
        $sql->bindParam(8, $ma_max, PDO::PARAM_STR);
        $sql->bindParam(9, $gc_min, PDO::PARAM_STR);
        $sql->bindParam(10, $gc_max, PDO::PARAM_STR);
        $sql->bindParam(11, $id_rango, PDO::PARAM_INT);
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "UPDATE `seg_reg_tecnico_fmto` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_fmto` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $id_rango, PDO::PARAM_INT);
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
