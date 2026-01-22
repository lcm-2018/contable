<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php");
    exit();
}
include '../../conexion.php';
$id_maestro = isset($_POST['id_maestro']) ? $_POST['id_maestro'] : exit('Acción no permitida');
$id_respon = $_POST['id_respon'];
$control_doc = $_POST['control'];
$cargo = $_POST['cargo_resp'];
$id_tercero = $_POST['id_tercero'];
$tipo_control = $control_doc == '0' ? NULL : $_POST['tipo_control'];
$tipo_control = $_POST['tipo_control'] == '4' ? $_POST['tipo_control'] : $tipo_control;
$fec_ini = $_POST['fecha_ini'];
$fec_fin = $_POST['fecha_fin'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$cambios = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($id_respon == 0) {
        $sql = "INSERT INTO `fin_respon_doc`
                        (`id_maestro_doc`,`id_tercero`,`cargo`,`tipo_control`,`fecha_ini`,`fecha_fin`,`id_user_reg`,`fec_reg`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_maestro, PDO::PARAM_INT);
        $sql->bindParam(2, $id_tercero, PDO::PARAM_INT);
        $sql->bindValue(3, $cargo, PDO::PARAM_STR);
        $sql->bindParam(4, $tipo_control, PDO::PARAM_INT);
        $sql->bindParam(5, $fec_ini, PDO::PARAM_STR);
        $sql->bindParam(6, $fec_fin, PDO::PARAM_STR);
        $sql->bindParam(7, $iduser, PDO::PARAM_INT);
        $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo 'ok';
        } else {
            echo $sql->errorInfo()[2];
        }
    } else {
        $query = "UPDATE `fin_respon_doc`
                    SET `id_tercero` = ?, `cargo` = ?, `tipo_control` = ?, `fecha_ini` = ?, `fecha_fin` = ?
                WHERE `id_respon_doc` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_tercero, PDO::PARAM_INT);
        $query->bindValue(2, $cargo, PDO::PARAM_STR);
        $query->bindParam(3, $tipo_control, PDO::PARAM_INT);
        $query->bindParam(4, $fec_ini, PDO::PARAM_STR);
        $query->bindParam(5, $fec_fin, PDO::PARAM_STR);
        $query->bindParam(6, $id_respon, PDO::PARAM_INT);
        if (!($query->execute())) {
            echo $query->errorInfo()[2];
        } else {
            if ($query->rowCount() > 0) {
                $query = "UPDATE `fin_respon_doc` SET `id_user_act` = ?, `fec_act` = ? WHERE `id_respon_doc` = ?";
                $query = $cmd->prepare($query);
                $query->bindParam(1, $iduser, PDO::PARAM_INT);
                $query->bindValue(2, $date->format('Y-m-d H:i:s'));
                $query->bindParam(3, $id_respon, PDO::PARAM_INT);
                $query->execute();
                if ($query->rowCount() > 0) {
                    echo 'ok';
                } else {
                    echo $query->errorInfo()[2];
                }
            } else {
                echo 'No se realizaron cambios';
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
