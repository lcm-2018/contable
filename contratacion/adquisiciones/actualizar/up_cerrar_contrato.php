<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_adq = isset($_POST['id_adq']) ? $_POST['id_adq'] : exit('Accion no permitida');
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

try {
    $estado = 9;
    $query = "UPDATE `ctt_adquisiciones` SET `estado` = ? WHERE `id_adquisicion` = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $estado, PDO::PARAM_INT);
    $query->bindParam(2, $id_adq, PDO::PARAM_INT);
    $query->execute();
    if ($query->rowCount() > 0) {
        echo 'ok';
    } else {
        echo 'Error al cerrar contrato' . $query->errorInfo()[2];
    }
    $cmd = NULL;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
