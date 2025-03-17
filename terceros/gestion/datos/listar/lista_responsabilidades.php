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
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_responsabilidad`, `codigo`, `descripcion`
            FROM
                `tb_responsabilidades_tributarias`";
    $rs = $cmd->query($sql);
    $respsonsabilidades = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($respsonsabilidades)) {
    foreach ($respsonsabilidades as $r) {
        $editar = $borrar = null;
        if (PermisosUsuario($permisos, 5201, 3) || $id_rol == 1) {
            $editar = $editar = '<button onclick="FormResponsabilidad(' . $r['id_responsabilidad'] . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
        }
        if (PermisosUsuario($permisos, 5201, 4) || $id_rol == 1) {
            $borrar = '<button onclick="BorrarResponsabilidad(' . $r['id_responsabilidad'] . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb" title="Borrar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        }
        $data[] = [
            'id' => $r['id_responsabilidad'],
            'codigo' => $r['codigo'],
            'descripcion' => $r['descripcion'],
            'botones' => '<div class="text-center">' . $editar . $borrar . '</div>'
        ];
    }
}

$datos = ['data' => $data];

echo json_encode($datos);
