<?php
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$iduser = $_SESSION['id_user'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_per_mod`, `id_usuario`, `id_modulo` FROM `seg_permisos_modulos` WHERE `id_usuario` = $iduser";
    $rs = $cmd->query($sql);
    $perm_modulos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    // consultar rol de usuario de la tabla seg_usuarios_sistema
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_opcion`
                , `per_consultar`
                , `per_adicionar`
                , `per_modificar`
                , `per_eliminar`
                , `per_anular`
                , `per_imprimir`
            FROM
                `seg_rol_usuario`
            WHERE (`id_usuario` = $iduser)";
    $rs = $cmd->query($sql);
    $permisos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    // consultar rol de usuario de la tabla seg_usuarios_sistema
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_rol` FROM `seg_usuarios_sistema` WHERE `id_usuario` = $iduser";
    $rs = $cmd->query($sql);
    $rol = $rs->fetch();
    $id_rol = $rol['id_rol'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

function PermisosUsuario($array, $opcion, $tipo)
{
    $comp = false;

    $key = array_search($opcion, array_column($array, 'id_opcion'));

    if ($key !== false) {
        if ($tipo == 0) {
            $comp = true;
        } else {
            $permiso = 'per_' . obtenerNombrePermiso($tipo);
            $comp = $array[$key][$permiso] == 1;
        }
    }

    return $comp;
}

function obtenerNombrePermiso($tipo)
{
    $permisos = [
        1 => 'consultar',
        2 => 'adicionar',
        3 => 'modificar',
        4 => 'eliminar',
        5 => 'anular',
        6 => 'imprimir',
    ];

    return $permisos[$tipo] ?? '';
}
