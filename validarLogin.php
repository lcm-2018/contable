<?php
session_start();
include 'conexion.php';
$res = array();
$usuario = $_POST['user'];
$contrasena = ($_POST['pass']);
$passlow = $_POST['passwd'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_usuario`
                ,`login`
                ,`clave`
                , CONCAT(`nombre1`, ' ', `apellido1`) as `nombre`
                ,`id_rol`
                , `estado` 
            FROM `seg_usuarios_sistema`  
            WHERE `login` = '$usuario'";
    $rs = $cmd->query($sql);
    $obj = $rs->fetch();
    if ($obj['login'] === $usuario && ($obj['clave'] === $contrasena || $obj['clave'] === $passlow)) {
        $_SESSION['id_user'] = $obj['id_usuario'];
        $_SESSION['user'] = $obj['nombre'];
        $_SESSION['login'] = $obj['login'];
        $_SESSION['rol'] = $obj['id_rol'];
        $_SESSION['navarlat'] = '0';
        $res['mensaje'] = 1;
        if ($obj['estado'] === '0') {
            $res['mensaje'] = 3;
        }
    } else {
        $res['mensaje'] = 0;
    }
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
