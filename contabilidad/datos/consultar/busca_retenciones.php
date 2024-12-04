<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../index.php');
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';

$buscar = mb_strtoupper($_POST['term']);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_retenciones`.`id_retencion`
                , CONCAT_WS(' -> ',`ctb_retencion_tipo`.`tipo`
                , `ctb_retenciones`.`nombre_retencion`) AS `retencion`
            FROM
                `ctb_retenciones`
                INNER JOIN `ctb_retencion_tipo` 
                    ON (`ctb_retenciones`.`id_retencion_tipo` = `ctb_retencion_tipo`.`id_retencion_tipo`)
            WHERE (`ctb_retencion_tipo`.`tipo` LIKE '%{$buscar}%') OR (`ctb_retenciones`.`nombre_retencion` LIKE '%{$buscar}%')";
    $rs = $cmd->query($sql);
    $retenciones = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
foreach ($retenciones as $lp) {
    $data[] = [
        'id' => $lp['id_retencion'],
        'label' => $lp['retencion'],
    ];
}
if (empty($data)) {
    $data[] = [
        'id' => '0',
        'label' => 'No hay coincidencias...',
    ];
}

echo json_encode($data);
