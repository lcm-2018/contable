<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
include '../../../terceros.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_tercero_api`, `nit_tercero`, `nom_tercero`
            FROM
                `tb_terceros`";
    $rs = $cmd->query($sql);
    $terceros = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
$buscar = mb_strtoupper($_POST['term']);
if ($buscar == '%%') {
    foreach ($terceros as $s) {
        $nom_tercero = mb_strtoupper($s['nom_tercero']);
        $data[] = [
            'id' => $s['id_tercero_api'],
            'label' => $nom_tercero,
        ];
    }
} else {
    foreach ($terceros as $s) {
        $nom_tercero = mb_strtoupper($s['nom_tercero']);
        $pos = strpos($nom_tercero, $buscar);
        if ($pos !== false) {
            $data[] = [
                'id' => $s['id_tercero_api'],
                'label' => $nom_tercero,
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
