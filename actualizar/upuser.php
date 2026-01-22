<?php

session_start();
include '../conexion.php';
$idus = isset($_POST['numIdUsuario']) ? $_POST['numIdUsuario'] : exit('Acción no permitida');
$nomb1 = $_POST['txtNombre1'];
$nomb2 = $_POST['txtNombre2'];
$ape1 = $_POST['txtApellido1'];
$ape2 = $_POST['txtApellido2'];
$login = $_POST['txtUsuario'];
$mail = $_POST['emailUsuario'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE seg_usuarios_sistema SET nombre1= ?, nombre2 = ?, apellido1 = ?, apellido2 = ?, login = ?, email = ? WHERE id_usuario = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $nomb1, PDO::PARAM_STR);
    $sql->bindParam(2, $nomb2, PDO::PARAM_STR);
    $sql->bindParam(3, $ape1, PDO::PARAM_STR);
    $sql->bindParam(4, $ape2, PDO::PARAM_STR);
    $sql->bindParam(5, $login, PDO::PARAM_STR);
    $sql->bindParam(6, $mail, PDO::PARAM_STR);
    $sql->bindParam(7, $idus, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $updata = 1;
    } else {
        $updata = 0;
    }
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    }
    if ($updata > 0) {
        echo '1';
    } else {
        echo 'No se registró ningún dato nuevo.';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
