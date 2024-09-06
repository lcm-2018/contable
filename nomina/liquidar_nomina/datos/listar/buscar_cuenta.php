<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$buscar = mb_strtoupper($_POST['term']);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_pgcp`,`cuenta`,`nombre`,`tipo_dato` 
            FROM `ctb_pgcp` 
            WHERE `cuenta` LIKE '%$buscar%' OR `nombre` LIKE '%$buscar%' 
            ORDER BY `cuenta`,`nombre` ASC";
    $rs = $cmd->query($sql);
    $cuentas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($cuentas)) {
    foreach ($cuentas as $c) {
        $cta = $c['cuenta'] . ' -> ' . $c['nombre'];
        $data[] = [
            'id' => $c['id_pgcp'],
            'label' => $cta,
            'tipo' => $c['tipo_dato'],
        ];
    }
} else {
    $data[] = [
        'id' => '0',
        'label' => 'No hay coincidencias...',
        'tipo' => '0',
    ];
}
echo json_encode($data);
