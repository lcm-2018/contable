<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$id = $_POST['id'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                    `ruta_doc`,`nombre_doc`
                FROM `ctt_documentos`
                WHERE `id_soportester` = $id";
    $rs = $cmd->query($sql);
    $pdf = $rs->fetch(PDO::FETCH_ASSOC);

    $query = "DELETE FROM `ctt_documentos` WHERE `id_soportester` = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id);
    $query->execute();
    if ($query->rowCount() > 0) {
        include '../../../financiero/reg_logs.php';
        $ruta = '../../../log';
        $consulta = "DELETE FROM `ctt_documentos` WHERE `id_soportester` = $id";
        RegistraLogs($ruta, $consulta);
        if (!empty($pdf)) {
            $filePath = $pdf['ruta_doc'] . $pdf['nombre_doc'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        echo 'ok';
    } else {
        echo $query->errorInfo()[2];
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
