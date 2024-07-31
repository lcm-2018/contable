<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';

$usuario = $_SESSION['id_user'];
$vigencia = $_SESSION['vigencia'];

$term = isset($_POST['term']) ? $_POST['term'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_pgcp AS id_cta,tipo_dato AS tipo,
                CONCAT_WS(' - ',cuenta,nombre) AS nom_cta
            FROM ctb_pgcp
            WHERE estado=1 AND CONCAT(cuenta,nombre) LIKE '%$term%'
            ORDER BY cuenta";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

foreach ($objs as $obj) {
    $data[] = [
        "id" => $obj['id_cta'],
        "label" => $obj['nom_cta'],
        "tipo" => $obj['tipo']
    ];
}

if (empty($data)) {
    $data[] = [
        "id" => '',
        "label" => 'No hay coincidencias...',
        "tipo" => ''
    ];
}
echo json_encode($data);
