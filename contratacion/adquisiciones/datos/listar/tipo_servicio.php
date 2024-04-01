<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$id_b_s = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_tipo_b_s`, `id_tipo_cotrato`
            FROM
                `tb_tipo_bien_servicio`
            INNER JOIN `tb_tipo_contratacion` 
                ON (`tb_tipo_bien_servicio`.`id_tipo_cotrato` = `tb_tipo_contratacion`.`id_tipo`)
            WHERE `id_tipo_b_s` = '$id_b_s'";
    $rs = $cmd->query($sql);
    $tipo_contrato = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$res = [];
if ($tipo_contrato['id_tipo_cotrato'] == '10') {
    $res['msg'] = 'ok';
} else {
    $res['msg'] = 'no';
}
echo json_encode($res);
