<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$data = file_get_contents("php://input");
include '../../../conexion.php';
// Inicio conexion a la base de datos
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
// Inicio transaccion 

try {
    $query = "DELETE FROM `pto_rad` WHERE `id_pto_rad` = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $data);
    $query->execute();
    if ($query->rowCount() > 0) {
        include '../../../financiero/reg_logs.php';
        $ruta = '../../../log';
        $sql = "DELETE FROM `pto_rad` WHERE `id_pto_rad` = $data";
        RegistraLogs($ruta, $sql);
        echo "ok";
    } else {
        echo $query->errorInfo()[2];
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
