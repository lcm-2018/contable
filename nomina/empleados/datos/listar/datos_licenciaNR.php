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
                id_licnr, fec_inicio, fec_fin, dias_inactivo, dias_habiles
            FROM
                nom_licenciasnr
            WHERE id_empleado ='$id'";
    $rs = $cmd->query($sql);
    $licenciasNR = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
include '../../../../permisos.php';
if (!empty($licenciasNR)) {
    foreach ($licenciasNR as $l) {
        $idLic = $l['id_licnr'];
        if ((PermisosUsuario($permisos, 5101, 3) || $id_rol == 1)) {
            $editar = '<button value="' . $idLic . '" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
        } else {
            $editar = null;
        }
        if ((PermisosUsuario($permisos, 5101, 4) || $id_rol == 1)) {
            $borrar = '<button value="' . $idLic . '" class="btn btn-outline-danger btn-sm btn-circle borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        } else {
            $borrar = null;
        }
        $data[] = [
            'id_lic' => $idLic,
            'fec_inicio' => $l['fec_inicio'],
            'fec_fin' => $l['fec_fin'],
            'dias_inactivo' => $l['dias_inactivo'],
            'dias_hab' => $l['dias_habiles'],
            'botones' => '<div class="center-block">' . $editar . $borrar . '</div>'
        ];
    }
} else {
    $data = [
        'id_lic' => '',
        'fec_inicio' => '',
        'fec_fin' => '',
        'dias_inactivo' => '',
        'dias_hab' => '',
        'botones' => '',
    ];
}

$datos = ['data' => $data];

echo json_encode($datos);
