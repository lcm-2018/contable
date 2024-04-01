<?php
session_start();
if (isset($_POST)) {
    $fecha = $_POST['fecha'];
    $vigencia = $_POST['vigencia'];
    $usuario = $_POST['usuario'];
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    include '../../conexion.php';
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($_POST['id'] != 0) {
        $query = $cmd->prepare("INSERT INTO tb_fin_fecha (vigencia,id_usuario,fecha) VALUES (?, ?, ?)");
        $query->bindParam(1, $vigencia, PDO::PARAM_INT);
        $query->bindParam(2, $usuario, PDO::PARAM_INT);
        $query->bindParam(3, $fecha, PDO::PARAM_STR);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $id = $cmd->lastInsertId();
            $response[] = array("value" => 'ok');
        } else {
            print_r($query->errorInfo()[2]);
        }
        $cmd = null;
    } else {
        $id = $_POST['id'];
        $query = $cmd->prepare("UPDATE tb_fin_fecha SET vigencia = ?, id_usuario = ?, fecha =? WHERE id = ?");
        $query->bindParam(1, $vigencia, PDO::PARAM_INT);
        $query->bindParam(2, $usuario, PDO::PARAM_INT);
        $query->bindParam(3, $fecha, PDO::PARAM_STR);
        $query->bindParam(4, $id, PDO::PARAM_INT);
        $query->execute();
        if ($query->rowCount() > 0) {
            $response[] = array("value" => 'modificado');
        } else {
            print_r($query->errorInfo()[2]);
        }
        $cmd = null;
        $response[] = array("value" => 'otro');
    }
    echo json_encode($response);
}
