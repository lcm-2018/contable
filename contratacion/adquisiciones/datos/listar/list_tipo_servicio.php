<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
$buscar = isset($_POST['term']) ? $_POST['term'] : exit('Acción no permitida ***');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_tipo_b_s`, `tipo_compra`, `tipo_contrato`, `tipo_bn_sv`, `objeto_definido`
            FROM
                `tb_tipo_bien_servicio`
            INNER JOIN `tb_tipo_contratacion` 
                ON (`tb_tipo_bien_servicio`.`id_tipo_cotrato` = `tb_tipo_contratacion`.`id_tipo`)
            INNER JOIN `tb_tipo_compra` 
                ON (`tb_tipo_contratacion`.`id_tipo_compra` = `tb_tipo_compra`.`id_tipo`)
            WHERE `tipo_bn_sv` LIKE '%$buscar%'
        ORDER BY `tipo_compra`, `tipo_contrato`, `tipo_bn_sv`";
    $rs = $cmd->query($sql);
    $tipo_servicio = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$data = [];
if (!empty($tipo_servicio)) {
    foreach ($tipo_servicio as $ts) {
        $tipo = $ts['tipo_compra'] . ' -> ' . $ts['tipo_contrato'] . ' -> ' . $ts['tipo_bn_sv'];
        $data[] = [
            'id' => $ts['id_tipo_b_s'],
            'label' => $tipo,
            'objeto' => $ts['objeto_definido'],
        ];
    }
} else {
    $data[] = [
        'id' => '0',
        'label' => 'No hay coincidencias...',
        'objeto' => '',
    ];
}
echo json_encode($data);
