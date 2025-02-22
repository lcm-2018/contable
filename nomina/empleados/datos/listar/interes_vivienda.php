<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
function pesos($valor)
{
    return '$ ' . number_format($valor, 2, ',', '.');
}
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida .-');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_intv`,`valor`,`fec_reg`
            FROM `nom_intereses_vivienda`
            WHERE (`id_empleado` = $id)";
    $rs = $cmd->query($sql);
    $vivienda = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                MAX(`id_intv`) AS `id_intv`
            FROM `nom_intereses_vivienda`
            WHERE (`id_empleado` = $id)";
    $rs = $cmd->query($sql);
    $max = $rs->fetch();
    $max = !empty($max['id_intv']) ? $max['id_intv'] : 1;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
include '../../../../permisos.php';
$data = [];
if (!empty($vivienda)) {
    foreach ($vivienda as $l) {
        $id_intv = $l['id_intv'];
        $borrar = null;
        $editar = null;
        if ($id_intv == $max) {
            if ((PermisosUsuario($permisos, 5101, 3) || $id_rol == 1)) {
                $editar = '<button onclick="FormIntVivienda(' . $id_intv . ')" class="btn btn-outline-primary btn-sm btn-circle" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
            }
            if ((PermisosUsuario($permisos, 5101, 4) || $id_rol == 1)) {
                $borrar = '<button onclick="EliminarIntVivienda(' . $id_intv . ')" class="btn btn-outline-danger btn-sm btn-circle" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
            }
        }
        $data[] = [
            'id' => $id_intv,
            'fecha' => $l['fec_reg'],
            'valor' => '<div class="text-right">$ ' . Number_format($l['valor'], 2, ',', '.') . '</div>',
            'botones' => '<div class="text-center">' . $editar . $borrar . '</div>'
        ];
    }
}

$datos = ['data' => $data];

echo json_encode($datos);
