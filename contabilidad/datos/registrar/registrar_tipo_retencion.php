<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$tipo = isset($_POST['txtTipoRte']) ? $_POST['txtTipoRte'] : exit('Acceso no autorizado');
$id_ret = $_POST['tipo_retencion'];
$id_tercero = $_POST['id_tercero'];
$estado = 1;
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($id_ret == 0) {
        $query = "INSERT INTO `ctb_retencion_tipo`
                    (`tipo`,`id_tercero`,`estado`,`id_user_reg`,`fecha_reg`)
                VALUES(?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $tipo, PDO::PARAM_STR);
        $query->bindParam(2, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(3, $estado, PDO::PARAM_INT);
        $query->bindParam(4, $iduser, PDO::PARAM_INT);
        $query->bindValue(5, $date->format('Y-m-d H:i:s'));
        $query->execute();
        $id = $cmd->lastInsertId();
        if ($cmd->lastInsertId() > 0) {
            echo 'ok';
        } else {
            echo $query->errorInfo()[2];
        }
    } else {
        $query = "UPDATE `ctb_retencion_tipo`
                    SET `tipo` = ?, `id_tercero` = ?
                WHERE `id_retencion_tipo` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $tipo, PDO::PARAM_STR);
        $query->bindParam(2, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(3, $id_ret, PDO::PARAM_INT);
        if (!($query->execute())) {
            echo $query->errorInfo()[2];
        } else {
            if ($query->rowCount() > 0) {
                $query = "UPDATE `ctb_retencion_tipo` SET `fec_act` = ?, `id_usuer_act` = ? WHERE `id_retencion_tipo` = ?";
                $query = $cmd->prepare($query);
                $query->bindValue(1, $date->format('Y-m-d H:i:s'));
                $query->bindParam(2, $iduser, PDO::PARAM_INT);
                $query->bindParam(3, $id_ret, PDO::PARAM_INT);
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
