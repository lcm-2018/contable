<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$id_rango = isset($_POST['id_rango']) ? $_POST['id_rango'] : exit('Acceso no autorizado');
$id_retencion = $_POST['id_retencion'];
$base = $_POST['valor_base'];
$valor_tope = $_POST['valor_tope'];
$tarifa = $_POST['tarifa'];
$id_vigencia = $_SESSION['id_vigencia'];
$estado = 1;
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($id_rango == 0) {
        $query = "INSERT INTO `ctb_retencion_rango`
                    (`id_vigencia`,`id_retencion`,`valor_base`,`valor_tope`,`tarifa`,`estado`,`id_user_reg`,`fecha_reg`)
                VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_vigencia, PDO::PARAM_INT);
        $query->bindParam(2, $id_retencion, PDO::PARAM_INT);
        $query->bindParam(3, $base, PDO::PARAM_STR);
        $query->bindParam(4, $valor_tope, PDO::PARAM_STR);
        $query->bindParam(5, $tarifa, PDO::PARAM_STR);
        $query->bindParam(6, $estado, PDO::PARAM_INT);
        $query->bindParam(7, $iduser, PDO::PARAM_INT);
        $query->bindValue(8, $date->format('Y-m-d H:i:s'));
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            echo 'ok';
        } else {
            echo $query->errorInfo()[2];
        }
    } else {
        $query = "UPDATE `ctb_retencion_rango`
                    SET `id_retencion` = ?, `valor_base` = ?, `valor_tope` = ?, `tarifa` = ?
                WHERE `id_rango` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_retencion, PDO::PARAM_INT);
        $query->bindParam(2, $base, PDO::PARAM_STR);
        $query->bindParam(3, $valor_tope, PDO::PARAM_STR);
        $query->bindParam(4, $tarifa, PDO::PARAM_STR);
        $query->bindParam(5, $id_rango, PDO::PARAM_INT);
        if (!($query->execute())) {
            echo $query->errorInfo()[2];
        } else {
            if ($query->rowCount() > 0) {
                $query = "UPDATE `ctb_retencion_rango` SET `fecha_act` = ?, `id_user_act` = ? WHERE `id_rango` = ?";
                $query = $cmd->prepare($query);
                $query->bindValue(1, $date->format('Y-m-d H:i:s'));
                $query->bindParam(2, $iduser, PDO::PARAM_INT);
                $query->bindParam(3, $id_rango, PDO::PARAM_INT);
                $query->execute();
                echo 'ok';
            } else {
                echo 'No se realizó ningún cambio';
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
