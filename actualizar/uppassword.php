<?php

session_start();
include '../conexion.php';
$id_user = $_SESSION['id_user'];
$pwd = isset($_POST['pwd']) ? $_POST['pwd'] : exit('Acción no permitida');
$newpwd = $_POST['newpwd'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_usuario`, `clave` FROM `seg_usuarios_sistema` WHERE `id_usuario` = '$id_user'";
    $rs = $cmd->query($sql);
    $obj = $rs->fetch();
    if ($obj['clave'] == $pwd) {
        $sql = "UPDATE `seg_usuarios_sistema` SET `clave` = ?, `fec_cambioclave` = ? WHERE `id_usuario` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $newpwd, PDO::PARAM_STR);
        $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(3, $id_user, PDO::PARAM_INT);
        $sql->execute();
        echo 'ok';
    } else {
        echo 'Contraseña actual incorrecta';
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
