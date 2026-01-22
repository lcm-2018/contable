<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                 ctb_pgcp.id_pgcp
                ,ctb_pgcp.cuenta
                ,ctb_pgcp.nombre
                ,ctb_pgcp.tipo_dato 
            FROM ctb_pgcp 
            WHERE ctb_pgcp.estado = 1";
    $rs = $cmd->query($sql);
    $obj_cuentas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$data = [];
$buscar = mb_strtoupper($_POST['term']);
if ($buscar == '%%') {
    foreach ($obj_cuentas as $obj) {
        $cuenta = mb_strtoupper($obj['cuenta']) . ' -> ' . $obj['nombre'];
        $data[] = [
            'id' => $obj['id_pgcp'],
            'label' => $cuenta,
        ];
    }
} else {
    foreach ($obj_cuentas as $obj) {
        $cuenta = mb_strtoupper($obj['cuenta']) . ' -> ' . $obj['nombre'];
        $pos = strpos($cuenta, $buscar);
        if ($pos !== false) {
            $data[] = [
                'id' => $obj['id_pgcp'],
                'label' => $cuenta,
            ];
        }
    }
}

if (empty($data)) {
    $data[] = [
        'id' => '0',
        'label' => 'No hay coincidencias...',
    ];
}
echo json_encode($data);
