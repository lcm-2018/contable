<?php session_start();
include 'conexion.php';
$res = [];
$usuario = $_POST['user'];
$contrasena = ($_POST['pass']);
$passlow = $_POST['passwd'];
$year = date('Y');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_vigencia`, `anio` FROM  `tb_vigencias` WHERE `id_vigencia` = (SELECT MAX(`id_vigencia`) FROM `tb_vigencias`)";
    $rs = $cmd->query($sql);
    $vigencia = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nit_ips` AS `nit`
                , `razon_social_ips` AS `nombre`
                , `caracter`
            FROM
                `tb_datos_ips`";
    $rs = $cmd->query($sql);
    $empresa = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
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
    $cmd = null;
    if (!empty($obj) && $obj['login'] === $usuario && ($obj['clave'] === $contrasena || $obj['clave'] === $passlow)) {
        $_SESSION['id_user'] = $obj['id_usuario'];
        $_SESSION['user'] = $obj['nombre'];
        $_SESSION['login'] = $obj['login'];
        $_SESSION['rol'] = $obj['id_rol'];
        $_SESSION['navarlat'] = '0';
        $_SESSION['caracter'] = $empresa['caracter'];
        $_SESSION['id_vigencia'] = $vigencia['id_vigencia'];
        $_SESSION['vigencia'] = $vigencia['anio'];
        $_SESSION['nit_emp'] = $empresa['nit'];
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
