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
                `id_retroactivo`, `fec_inicio`, `fec_final`, `meses`, `porcentaje`, `observaciones`, `vigencia`, `estado`
            FROM
                `nom_retroactivos`
            WHERE `vigencia` = '$vigencia'";
    $rs = $cmd->query($sql);
    $retroactivos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$datos = [];
foreach ($retroactivos as $ra) {
    $id = $ra['id_retroactivo'];
    $detalles = '<a value="' . $id . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles mensual liquidación retroactivo "><span class="fas fa-calendar-alt fa-lg"></span></a>';
    $datos[] = array(
        'id' => $id,
        'inicia' => $ra['fec_inicio'],
        'termina' => $ra['fec_final'],
        'meses' => $ra['meses'],
        'incremento' => $ra['porcentaje'] . '%',
        'observa' => $ra['observaciones'],
        'botones' => '<div class="text-center">' . $detalles . '</div>'
    );
}
$data = [
    'data' => $datos
];
echo json_encode($data);
