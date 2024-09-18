<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}

include '../conexion.php';
include '../permisos.php';
$key = array_search('10', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_consulta`,`nombre`, `parametros`, `consulta`, `fec_reg`
            FROM
                `seg_consultas_sql`";
    $rs = $cmd->query($sql);
    $consultas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($consultas)) {
    foreach ($consultas as $key => $value) {
        $borrar = $ejecuta = null;
        if ((intval($permisos['borrar'])) == 1) {
            $borrar = '<a value="' . $value['id_consulta'] . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $ejecuta = '<a value="' . $value['id_consulta'] . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb ejecuta" title="Ejecutar"><span class="fas fa-play"></span></a>';
        $data[] = [
            'id_consulta' => $value['id_consulta'],
            'nombre' => $value['nombre'],
            'fec_reg' => $value['fec_reg'],
            'botones' => '<div class="text-center">' . $ejecuta . $borrar . '</div>'
        ];
    }
}
$datos = ['data' => $data];

echo json_encode($datos);
