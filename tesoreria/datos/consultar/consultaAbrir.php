<?php

include '../../../conexion.php';
session_start();
$data = file_get_contents("php://input");
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha = $date->format('Y-m-d H:i:s');
$estado = '1';
// update ctb_libaux set estado='C' where id_ctb_doc=$data;
// Realizo conexion con la base de datos
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `ctb_doc`
                SET `estado` = ?, `id_user_act` = ?, `fecha_act` = ?
            WHERE `id_ctb_doc` = ?";
    $query = $cmd->prepare($sql);
    $query->bindParam(1, $estado, PDO::PARAM_INT);
    $query->bindParam(2, $id_user, PDO::PARAM_INT);
    $query->bindParam(3, $fecha, PDO::PARAM_STR);
    $query->bindParam(4, $data, PDO::PARAM_INT);
    $query->execute();
    if ($query->rowCount() > 0) {
        echo "ok";
    } else {
        echo "error: " . $cmd->errorInfo()[2];
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
