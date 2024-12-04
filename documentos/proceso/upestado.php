<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php");
    exit();
}
include '../../conexion.php';
$data = isset($_POST['data']) ? $_POST['data'] : exit('Acción no permitida');
$data = explode('|', base64_decode($data));
$id_maestro = $data[0];
$estado = $data[1] == 1 ? 0 : 1;
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `fin_maestro_doc` SET `estado` = ?, `id_user_act` = ?, `fecha_act` = ? WHERE `id_maestro` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $estado, PDO::PARAM_INT);
    $sql->bindParam(2, $iduser, PDO::PARAM_INT);
    $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(4, $id_maestro, PDO::PARAM_INT);
    if ($sql->execute()) {
        $query = "UPDATE `fin_respon_doc` SET `estado` = ?, `id_user_act` = ?, `fec_act` = ? WHERE `id_maestro_doc` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $estado, PDO::PARAM_INT);
        $query->bindParam(2, $iduser, PDO::PARAM_INT);
        $query->bindValue(3, $date->format('Y-m-d H:i:s'));
        $query->bindParam(4, $id_maestro, PDO::PARAM_INT);
        if ($query->execute()) {
            echo 'ok';
        } else {
            echo $query->errorInfo()[2];
        }
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
