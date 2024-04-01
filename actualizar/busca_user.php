<?php

session_start();
include '../conexion.php';
$user = isset($_POST['term']) ? $_POST['term'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_usuario`, `documento`, CONCAT_WS(' ', `nombre1`, `nombre2`, `apellido1`, `apellido2`) AS `nombre`, `login`
            FROM
                `seg_usuarios_sistema`
            WHERE `documento` LIKE '%$user%' OR `nombre1` LIKE '%$user%' OR `nombre2` LIKE '%$user%' 
                OR `apellido1` LIKE '%$user%' OR `apellido2` LIKE '%$user%' OR `login` LIKE '%$user%'";
    $rs = $cmd->query($sql);
    $resps = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
foreach ($resps as $r) {
    $data[] = [
        'id' => $r['id_usuario'],
        'label' => $r['documento'] . ' || ' . $r['nombre'] . ' || ' . $r['login'],
    ];
}

if (empty($data)) {
    $data[] = [
        'id' => '0',
        'label' => 'No hay coincidencias...',
    ];
}
echo json_encode($data);
