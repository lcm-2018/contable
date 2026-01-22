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
                `tb_terceros`.`id_tercero`
                , `tb_terceros`.`tipo_doc`
                , `tb_terceros`.`nit_tercero`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`estado`
                , `tb_terceros`.`id_tercero_api`
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
        $nom_tercero = mb_strtoupper($s['nom_tercero']) . ' -> ' . $s['nit_tercero'];
        $data[] = [
            'id' => $s['id_tercero_api'],
            'label' => $nom_tercero,
        ];
    }
} else {
    foreach ($terceros as $s) {
        $nom_tercero = mb_strtoupper($s['nom_tercero']) . ' -> ' . $s['nit_tercero'];
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
