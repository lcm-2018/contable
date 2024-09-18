<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
include '../../../conexion.php';
$search = isset($_POST['search']) ?  $_POST['search'] : '';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_pgcp`, `cuenta`,`nombre`,`tipo_dato` 
            FROM `ctb_pgcp` 
            WHERE (`cuenta` LIKE '$search%' OR `nombre` LIKE '$search%') AND `estado` = 1";
    $rs = $cmd->query($sql);
    $cuentas = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (empty($cuentas)) {
    $response[] = [
        'id' => '0',
        'label' => 'No hay coincidencias...',
        'tipo_dato' => '0',
    ];
} else {
    foreach ($cuentas as $c) {
        $response[] = [
            'id' => $c['id_pgcp'],
            'label' => $c['cuenta'] . ' - ' . $c['nombre'],
            'tipo_dato' => $c['tipo_dato'],
        ];
    }
}
echo json_encode($response);

exit;
