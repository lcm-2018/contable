<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$id_nota = isset($_POST['id_nota']) ? $_POST['id_nota'] : exit('Acción no permitida');
$id_tipo_nota = $_POST['slcNota'];
$fecha_n = $_POST['fecNota'];
$valor = $_POST['numValNota'];
$observaciones = $_POST['txtObservaNota'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_notas_acfijo` SET `id_tipo_n` = ?, `fecha_n` = ?, `valor` = ?, `observacion` = ? WHERE `id_nota` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_tipo_nota, PDO::PARAM_INT);
    $sql->bindParam(2, $fecha_n, PDO::PARAM_STR);
    $sql->bindParam(3, $valor, PDO::PARAM_STR);
    $sql->bindParam(4, $observaciones, PDO::PARAM_STR);
    $sql->bindParam(5, $id_nota, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `seg_notas_acfijo` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_nota` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_user, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_nota, PDO::PARAM_INT);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo 1;
            } else {
                echo $sql->errorInfo()[2];
                exit();
            }
        } else {
            echo 'No se ingresó ningún dato nuevo';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
