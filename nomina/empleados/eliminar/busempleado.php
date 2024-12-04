<?php

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$res = "";
$id = isset($_POST['idempleado']) ? $_POST['idempleado'] : exit('Acción no permitida');
$_SESSION['del'] = $id;

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT no_documento, CONCAT(nombre1, ' ', nombre2) as nombres, CONCAT(apellido1, ' ', apellido2) as apellidos FROM nom_empleado  WHERE id_empleado='$id'";
    $rs = $cmd->query($sql);
    $obj = $rs->fetch();
    $res .="Seguro desea eliminar?"
            .'<br>'.'<br>'
            ."ID: ".$obj['no_documento']
            .'<br>'
            ."Nombres:  ".$obj['nombres']
            .'<br>'
            ."Apellidos: ".$obj['apellidos'];
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo $res;