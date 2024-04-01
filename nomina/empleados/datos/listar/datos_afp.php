<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}

include '../../../../conexion.php';
include '../../../../permisos.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT id_novafp, nom_novedades_afp.id_afp, nombre_afp, CONCAT(nit_afp, '-',dig_verf) AS nitafp, fec_afiliacion, nom_novedades_afp.fec_retiro
            FROM
                nom_novedades_afp
            INNER JOIN nom_afp 
                ON (nom_novedades_afp.id_afp = nom_afp.id_afp)
            INNER JOIN nom_empleado 
                ON (nom_novedades_afp.id_empleado = nom_empleado.id_empleado)
            WHERE nom_empleado.id_empleado = '$id'
            ORDER BY fec_afiliacion ASC";
    $rs = $cmd->query($sql);
    $afp = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$hoy = $date->format('Y-m-d');
if (!empty($afp)) {
    foreach ($afp as $a) {
        if ((PermisosUsuario($permisos, 5101, 3) || $id_rol == 1)) {
            $editar = '<button value="' . $a['id_novafp'] . '" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
        } else {
            $editar = null;
        }
        if ((PermisosUsuario($permisos, 5101, 4) || $id_rol == 1)) {
            $borrar = '<button value="' . $a['id_novafp'] . '" class="btn btn-outline-danger btn-sm btn-circle borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        } else {
            $borrar = null;
        }
        if ($a['fec_retiro'] != ''  && $a['fec_retiro'] <= $hoy) {
            $editar = $borrar = null;
        }
        $data[] = [
            'id_novafp' => $a['id_novafp'],
            'nombre_afp' => $a['nombre_afp'],
            'nitafp' => $a['nitafp'],
            'fec_afiliacion' => $a['fec_afiliacion'],
            'fec_retiro' => $a['fec_retiro'],
            'botones' => '<div class="center-block">' . $editar . $borrar . '</div>'
        ];
    }
} else {
    $data = [
        'id_novafp' => '',
        'nombre_afp' => '',
        'nitafp' => '',
        'fec_afiliacion' => '',
        'fec_retiro' => '',
        'botones' => '',
    ];
}

$datos = ['data' => $data];

echo json_encode($datos);
