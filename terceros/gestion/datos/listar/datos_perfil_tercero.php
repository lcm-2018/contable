<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_perfil`,`descripcion` FROM `ctt_perfil_tercero`";
    $rs = $cmd->query($sql);
    $perfiles = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

if (!empty($perfiles)) {
    foreach ($perfiles as $d) {
        $id_perfil = $d['id_perfil'];
        $borrar = $editar = '';

        if (PermisosUsuario($permisos, 5201, 3) || $id_rol == 1) {
            $editar = '<a onclick="EditarPerfilTercero(' . $id_perfil . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb "  title="Editar perfil de tercero"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5201, 4) || $id_rol == 1) {
            $borrar = '<a onclick="BorrarPerfilTercero(' . $id_perfil . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            'id' => $id_perfil,
            'descripcion' => mb_strtoupper($d['descripcion']),
            'acciones' => '<div class="text-center">' . $editar . $borrar . '</div>',
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);
