<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';

$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_inc`, `porcentaje`, `vigencia`, `fecha`, `estado`
            FROM
                `nom_incremento_salario`
            WHERE (`vigencia` = '$vigencia')";
    $rs = $cmd->query($sql);
    $incrementos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$datos = [];
foreach ($incrementos as $inc) {
    $id = $inc['id_inc'];
    if (PermisosUsuario($permisos, 5114, 4) || $id_rol == 1) {
        $eliminar = '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb eliminar" title="Eliminar concepto"><span class="fas fa-trash-alt fa-lg"></span></a>';
    }
    $estado =  $inc['estado'] == 0 ? '<span class="badge badge-secondary">Inactivo</span>' : '<span class="badge badge-success">Activo</span>';
    $datos[] = array(
        'id' => $id,
        'porcentaje' => '<div class="text-right">' . $inc['porcentaje'] . ' %</div>',
        'fecha' => '<div class="text-right">' . $inc['fecha'] . '</div>',
        'estado' => '<div class="text-center">' . $estado . '</div>',
        'botones' => '<div class="text-center">' . $eliminar . '</div>'
    );
}
$data = [
    'data' => $datos
];
echo json_encode($data);
