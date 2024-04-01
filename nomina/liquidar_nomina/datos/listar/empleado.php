<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$busca = isset($_POST['term']) ? $_POST['term'] : exit('Acción no permitida');

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_empleado`, `no_documento`, CONCAT_WS(' ', `apellido1`, `apellido2`, `nombre1`, `nombre2`) AS `nombre`
            FROM
                `nom_empleado`
            WHERE `no_documento` LIKE '%$busca%' OR `apellido1` LIKE '%$busca%' OR `apellido2` LIKE '%$busca%' OR `nombre1` LIKE '%$busca%' OR `nombre2` LIKE '%$busca%'";
    $rs = $cmd->query($sql);
    $datos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
foreach ($datos as $de) {
    $doc = $de['no_documento'];
    $nombre = strtoupper($de['nombre']);
    $data[] = [
        'id' => $de['id_empleado'],
        'label' => $doc . ' - ' . $nombre,
    ];
}
if (empty($datos)) {
    $data[] = [
        'id' => '0',
        'label' => 'No hay coincidencias...',
    ];
}
echo json_encode($data);
