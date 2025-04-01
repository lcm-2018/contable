<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
include '../../conexion.php';

$buscar = mb_strtoupper($_POST['term']);
$where = '';
if ($buscar != '%%') {
    $where = "AND `tb_terceros`.`nom_tercero` LIKE '%$buscar%' OR `tb_terceros`.`nit_tercero` LIKE '%$buscar%'";
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tb_terceros`.`id_tercero_api` AS `id`
                , `tb_terceros`.`nom_tercero` As `nombre` 
                , IFNULL(`tb_terceros`.`nit_tercero`,'') As `cedula`
            FROM
                `tb_terceros`
            WHERE `tb_terceros`.`id_tercero_api` > 0 $where
            ORDER BY `tb_terceros`.`nom_tercero` ASC";
    $rs = $cmd->query($sql);
    $terceros = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];

if (empty($terceros)) {
    $data[] = [
        'id' => '0',
        'nombre' => 'No hay coincidencias...',
        'cedula' => '',
    ];
} else {
    $data = $terceros;
}
echo json_encode($data);
