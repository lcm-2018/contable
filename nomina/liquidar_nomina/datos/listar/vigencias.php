<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_vigencia`, `anio`
            FROM
                `tb_vigencias`
            WHERE (`anio`  >= 2023)";
    $rs = $cmd->query($sql);
    $vigencias = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$datos = [];
foreach ($vigencias as $vg) {
    $actualizar = $eliminar = null;
    $id = $vg['id_vigencia'];
    if ($_SESSION['id_user'] == '1') {
        // $actualizar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb actualizar" title="Actualizar Vigencia"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        //$eliminar = '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb eliminar" title="Eliminar concepto"><span class="fas fa-trash-alt fa-lg"></span></a>';
    }
    $datos[] = array(
        'vigencia' => $vg['anio'],
        'botones' => '<div class="text-center">' . $actualizar . $eliminar . '</div>'
    );
}
$data = [
    'data' => $datos
];
echo json_encode($data);
