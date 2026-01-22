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
    $sql = "SELECT id_novedad, nom_epss.id_eps, nombre_eps, CONCAT(nit, '-', digito_verific) AS nit, fec_afiliacion, fec_retiro
            FROM
                nom_novedades_eps
            INNER JOIN nom_epss 
                ON (nom_novedades_eps.id_eps = nom_epss.id_eps)
            WHERE id_empleado = '$id'
                ORDER BY fec_afiliacion DESC";
    $rs = $cmd->query($sql);
    $eps = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$hoy = $date->format('Y-m-d');
include '../../../../permisos.php';
if (!empty($eps)) {
    foreach ($eps as $e) {
        if ((PermisosUsuario($permisos, 5101, 3) || $id_rol == 1)) {
            $editar = '<button value="' . $e['id_novedad'] . '" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
        } else {
            $editar = null;
        }
        if ((PermisosUsuario($permisos, 5101, 4) || $id_rol == 1)) {
            $borrar = '<button value="' . $e['id_novedad'] . '" class="btn btn-outline-danger btn-sm btn-circle borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        } else {
            $borrar = null;
        }
        if ($e['fec_retiro'] != ''  && $e['fec_retiro'] <= $hoy) {
            $editar = $borrar = null;
        }
        $data[] = [
            'id_novedad' => $e['id_novedad'],
            'nombre_eps' => $e['nombre_eps'],
            'nit' => $e['nit'],
            'fec_afiliacion' => $e['fec_afiliacion'],
            'fec_retiro' => $e['fec_retiro'],
            'botones' => '<div class="center-block">' . $editar . $borrar . '</div>'
        ];
    }
} else {
    $data = [
        'id_novedad' => '',
        'nombre_eps' => '',
        'nit' => '',
        'fec_afiliacion' => '',
        'fec_retiro' => '',
        'botones' => ''
    ];
}

$datos = ['data' => $data];

echo json_encode($datos);
