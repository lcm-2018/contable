<?php

session_start();
include '../conexion.php';
$datos = isset($_POST['ids']) ? explode('|', $_POST['ids']) : exit('Acción no permitida');
$id_user = $_POST['id_user'];
$id_opcion = $datos[0];
$tipo = $datos[1];
$estado = $datos[2];
switch ($tipo) {
    case 1:
        $tpermiso = 'per_consultar';
        break;
    case 2:
        $tpermiso = 'per_adicionar';
        break;
    case 3:
        $tpermiso = 'per_modificar';
        break;
    case 4:
        $tpermiso = 'per_eliminar';
        break;
    case 5:
        $tpermiso = 'per_anular';
        break;
    case 6:
        $tpermiso = 'per_imprimir';
        break;
    default:
        exit('No permitido');
        break;
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_usuario`
            FROM
                `seg_rol_usuario`
            WHERE (`id_usuario` = $id_user AND `id_opcion` = $id_opcion)";
    $rs = $cmd->query($sql);
    $existe = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    if (empty($existe)) {
        $val = 1;
        $sql = "INSERT INTO `seg_rol_usuario` (`id_usuario`, `id_opcion`, `$tpermiso`) VALUES (?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_user);
        $sql->bindParam(2, $id_opcion);
        $sql->bindParam(3, $val);
        $sql->execute();
        $res = $sql->rowCount();
    } else {
        $val = $estado == 0 ? 1 : 0;
        $sql = "UPDATE `seg_rol_usuario`  SET `$tpermiso` = ? WHERE `id_usuario` = ? AND `id_opcion` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $val);
        $sql->bindParam(2, $id_user);
        $sql->bindParam(3, $id_opcion);
        $sql->execute();
        $res = $sql->rowCount();
    }
    if ($res > 0) {
        echo 'ok';
    } else {
        echo $cmd->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
