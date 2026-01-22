<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM
                nom_incapacidad
            INNER JOIN nom_tipo_incapacidad 
                ON (nom_incapacidad.id_tipo = nom_tipo_incapacidad.id_tipo) 
            WHERE id_empleado ='$id'";
    $rs = $cmd->query($sql);
    $incapacidades = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
include '../../../../permisos.php';
if (!empty($incapacidades)) {
    foreach ($incapacidades as $i) {
        $idIncap = $i['id_incapacidad'];
        if ((PermisosUsuario($permisos, 5101, 3) || $id_rol == 1)) {
            $editar = '<button value="' . $idIncap . '" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
        } else {
            $editar = null;
        }
        if ((PermisosUsuario($permisos, 5101, 4) || $id_rol == 1)) {
            $borrar = '<button value="' . $idIncap . '" class="btn btn-outline-danger btn-sm btn-circle borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        } else {
            $borrar = null;
        }
        $categoria = $i['categoria'] == 1 ? 'INICIAL' : 'PRÓRROGA';
        $estado = $i['categoria'] == 1 ? 'primary' : 'info';
        $data[] = [
            'id_incap' => $idIncap,
            'tipo' => mb_strtoupper($i['tipo']),
            'fec_inicio' => $i['fec_inicio'],
            'fec_fin' => $i['fec_fin'],
            'dias' => $i['can_dias'],
            'valor' => '<div class="text-center"><span class="badge badge-pill badge-' . $estado . '">' . $categoria . '</span></div>',
            'botones' => '<div class="center-block">' . $editar . $borrar . '</div>'
        ];
    }
} else {
    $data = [
        'id_incap' => '',
        'tipo' => '',
        'fec_inicio' => '',
        'fec_fin' => '',
        'dias' => '',
        'valor' => '',
        'botones' => '',
    ];
}

$datos = ['data' => $data];

echo json_encode($datos);
