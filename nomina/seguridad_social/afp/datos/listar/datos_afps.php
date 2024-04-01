<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../../index.php");</script>';
    exit();
}
include '../../../../../conexion.php';
include '../../../../../permisos.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT id_afp, nit_afp, dig_verf, nombre_afp, telefono, correo FROM nom_afp";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($obj)) {
    foreach ($obj as $o) {
        $id_afp = $o['id_afp'];
        if ((intval($permisos['editar'])) === 1) {
            $editar = '<a value="' . $id_afp . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        } else {
            $editar = null;
        }
        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<a value="' . $id_afp . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        } else {
            $borrar = null;
        }
        $data[] = [
            'nombre' => mb_strtoupper($o['nombre_afp']),
            'nit' => $o['nit_afp'] . '-' . $o['dig_verf'],
            'telefono' => $o['telefono'],
            'correo' =>  $o['correo'],
            'botones' => '<div class="text-center">' . $editar . $borrar . '</div>',
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);
