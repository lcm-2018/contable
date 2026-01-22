<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$usuario = $_SESSION['id_user'];
$term = isset($_POST['term']) ? $_POST['term'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_prescom,nom_presentacion,IFNULL(cantidad,1) AS cantidad
            FROM far_presentacion_comercial
            WHERE nom_presentacion LIKE '%$term%'
<<<<<<< HEAD
            ORDER BY nom_presentacion";
=======
            ORDER BY IF(id_prescom=0,0,CONCAT('1',nom_presentacion))";
>>>>>>> d750d9bf66c1ebfb0ab684f97d76cc2d83a9799b
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

foreach ($objs as $obj) {
    $data[] = [
        "id" => $obj['id_prescom'],
        "label" => $obj['nom_presentacion'],
        "cantidad" => $obj['cantidad'],
    ];
}

if (empty($data)) {
    $data[] = [
        "id" => '',
        "label" => 'No hay coincidencias...',
    ];
}
echo json_encode($data);
