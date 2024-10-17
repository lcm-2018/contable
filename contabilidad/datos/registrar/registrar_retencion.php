<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$tipo = isset($_POST['txtTipoRte']) ? $_POST['txtTipoRte'] : exit('Acceso no autorizado');
$id_ret = $_POST['id_retencion'];
$nombre = $_POST['txtNombreRte'];
$cuenta = $_POST['id_codigoCta'];
$estado = 1;
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($id_ret == 0) {
        $query = "INSERT INTO `ctb_retenciones`
                    (`id_retencion_tipo`,`nombre_retencion`,`id_cuenta`,`estado`,`id_user_reg`,`fecha_reg`)
                VALUES (?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $tipo, PDO::PARAM_INT);
        $query->bindParam(2, $nombre, PDO::PARAM_STR);
        $query->bindParam(3, $cuenta, PDO::PARAM_INT);
        $query->bindParam(4, $estado, PDO::PARAM_INT);
        $query->bindParam(5, $iduser, PDO::PARAM_INT);
        $query->bindValue(6, $date->format('Y-m-d H:i:s'));
        $query->execute();
        $id = $cmd->lastInsertId();
        if ($cmd->lastInsertId() > 0) {
            echo 'ok';
        } else {
            echo $query->errorInfo()[2];
        }
    } else {
        $query = "UPDATE `ctb_retenciones`
                    SET `id_retencion_tipo` = ?, `nombre_retencion` = ?, `id_cuenta` = ?
                WHERE `id_retencion` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $tipo, PDO::PARAM_INT);
        $query->bindParam(2, $nombre, PDO::PARAM_STR);
        $query->bindParam(3, $cuenta, PDO::PARAM_INT);
        $query->bindParam(4, $id_ret, PDO::PARAM_INT);
        if (!($query->execute())) {
            echo $query->errorInfo()[2];
        } else {
            if ($query->rowCount() > 0) {
                $query = "UPDATE `ctb_retenciones` SET `fecha_act` = ?, `id_user_act` = ? WHERE `id_retencion` = ?";
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
