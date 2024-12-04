<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT
                `nom_ccosto_empleado`.`id_cc_emp`
                , `tb_centrocostos`.`nom_centro`
                , `nom_ccosto_empleado`.`fec_reg`
            FROM
                `nom_ccosto_empleado`
                INNER JOIN `tb_centrocostos` 
                    ON (`nom_ccosto_empleado`.`id_ccosto` = `tb_centrocostos`.`id_centro`)
            WHERE (`nom_ccosto_empleado`.`id_empleado` = $id)";
    $rs = $cmd->query($sql);
    $ccostos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$hoy = $date->format('Y-m-d');
if (!empty($ccostos)) {
    foreach ($ccostos as $a) {
        if ((PermisosUsuario($permisos, 5101, 3) || $id_rol == 1)) {
            $editar = '<button value="' . $a['id_cc_emp'] . '" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
        } else {
            $editar = null;
        }
        if ((PermisosUsuario($permisos, 5101, 4) || $id_rol == 1)) {
            $borrar = '<button value="' . $a['id_cc_emp'] . '" class="btn btn-outline-danger btn-sm btn-circle borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        } else {
            $borrar = null;
        }
        $data[] = [
            'id' => $a['id_cc_emp'],
            'nombre' => $a['nom_centro'],
            'fecha' => date('Y-m-d', strtotime($a['fec_reg'])),
            'botones' => '<div class="center-block">' . $editar . $borrar . '</div>'
        ];
    }
} else {
    $data = [
        'id_cc_emp' => '',
        'nombre_arl' => '',
        'nitarl' => '',
        'riesgo' => '',
        'fec_afiliacion' => '',
        'fec_retiro' => '',
        'botones' => '',
    ];
}

$datos = ['data' => $data];

echo json_encode($datos);
