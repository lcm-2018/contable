<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../index.php');
    exit();
}
include '../../../conexion.php';

$id = isset($_POST['id_pto_doc']) ? $_POST['id_pto_doc'] : exit('Acceso no disponible');
$tipo = $_POST['tipo'];
$fecha = $_POST['fecha'];
$motivo = $_POST['objeto'];
$id_user = $_SESSION['id_user'];
$table = $tipo == 'cdp' ? 'pto_cdp' : 'pto_crp';
$estado = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE $table 
                SET `estado`= ?, `id_user_anula` = ?, `fecha_anula` = ?, `concepto_anula` = ? 
            WHERE `id_pto_$tipo` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $estado, PDO::PARAM_INT);
    $sql->bindParam(2, $id_user, PDO::PARAM_INT);
    $sql->bindParam(3, $fecha, PDO::PARAM_STR);
    $sql->bindParam(4, $motivo, PDO::PARAM_STR);
    $sql->bindParam(5, $id, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        if ($tipo == 'cdp') {
            $sql = "UPDATE `ctt_adquisiciones` SET `id_cdp` = NULL WHERE `id_cdp` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id, PDO::PARAM_INT);
            $sql->execute();
        }
        echo 'ok';
    } else {
        echo $sql->errorInfo()[2];
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
