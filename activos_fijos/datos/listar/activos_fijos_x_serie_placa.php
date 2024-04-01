<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$busca = isset($_POST['term']) ? $_POST['term'] : exit('Acción no permitida');
$tipo = $_POST['tbus'];
if ($tipo == '1') {
    $sql = "SELECT `id_serial`, `id_activo_fijo`, `placa` as `res`,  `tipo_entra` FROM  `seg_num_serial` WHERE `placa` LIKE '%$busca%'";
} else {
    $sql = "SELECT `id_serial`, `id_activo_fijo`, `num_serial` as `res`, `tipo_entra` FROM  `seg_num_serial` WHERE `num_serial` LIKE '%$busca%'";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $rs = $cmd->query($sql);
    $datos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
foreach ($datos as $ls) {
    $data[] = [
        'id' => $ls['id_serial'],
        'tipo' => $ls['tipo_entra'],
        'label' => $ls['res'],
    ];
}
if (empty($data)) {
    $data[] = [
        'id' => '0',
        'tipo' => '',
        'label' => 'No hay coincidencias...',
    ];
}
echo json_encode($data);
