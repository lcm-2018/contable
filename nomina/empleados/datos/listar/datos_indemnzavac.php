<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_indemniza`, `id_empleado`, `fec_inica`, `fec_fin`, `cant_dias`, `estado`
            FROM
                `nom_indemniza_vac`
            WHERE (`id_empleado` = $id)";
    $rs = $cmd->query($sql);
    $indemnizaciones = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
include '../../../../permisos.php';
if (!empty($indemnizaciones)) {
    foreach ($indemnizaciones as $in) {
        $idIn = $in['id_indemniza'];
        if ((PermisosUsuario($permisos, 5101, 3) || $id_rol == 1)) {
            $editar = '<button value="' . $idIn . '" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
        } else {
            $editar = null;
        }
        if ((PermisosUsuario($permisos, 5101, 4) || $id_rol == 1)) {
            $borrar = '<button value="' . $idIn . '" class="btn btn-outline-danger btn-sm btn-circle borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        } else {
            $borrar = null;
        }
        $estado = $in['estado'] == 1 ? '<span class="badge badge-pill badge-warning">PENDIENTE</span>' : '<span class="badge badge-pill badge-secondary">LIQUIDADO</span>';
        $data[] = [
            'fec_inicio' => $in['fec_inica'],
            'fec_fin' => $in['fec_fin'],
            'dias_indemniza' => $in['cant_dias'],
            'estado' => '<div class= "text-center">' . $estado . '</div>',
            'botones' => '<div class="text-center">' . $editar . $borrar . '</div>'
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);
