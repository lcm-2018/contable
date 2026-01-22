<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$formato = isset($_POST['slcTipoFormato']) ? $_POST['slcTipoFormato'] : exit('Acceso denegado');
$tipo_bn_sv = $_POST['slcTipoBnSv'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

if (!isset($_FILES['fileContratacion']) || $_FILES['fileContratacion']['error'] !== UPLOAD_ERR_OK) {
    exit('Error al subir el archivo');
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    // consulta para insertar el formato
    $sql = "SELECT `id_relacion` FROM `ctt_formatos_doc_rel` WHERE `id_formato` = ? AND `id_tipo_bn_sv` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $formato, PDO::PARAM_INT);
    $sql->bindParam(2, $tipo_bn_sv, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        exit('El formato que se intenta registrar ya existe');
    }
    $sql = "INSERT INTO `ctt_formatos_doc_rel`
                (`id_formato`,`id_tipo_bn_sv`,`id_user_reg`,`fec_reg`)
            VALUES (?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $formato, PDO::PARAM_INT);
    $sql->bindParam(2, $tipo_bn_sv, PDO::PARAM_INT);
    $sql->bindParam(3, $id_user, PDO::PARAM_INT);
    $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        $id = $cmd->lastInsertId();
        $file_tmp = $_FILES['fileContratacion']['tmp_name'];
        $file_dest = '../../adquisiciones/soportes/' . $id . '.docx';
        if (!move_uploaded_file($file_tmp, $file_dest)) {
            exit('Error al mover el archivo');
        } else {
            chmod($file_dest, 0777);
            echo 'ok';
        }
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
