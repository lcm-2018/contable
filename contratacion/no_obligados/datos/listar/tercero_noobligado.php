<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';

$doc = $_POST['noDoc'];
$res = [];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_tercero`, `id_tdoc`, `no_doc`, `nombre`, `procedencia`, `tipo_org`, `reg_fiscal`, `resp_fiscal`, `correo`, `telefono`, `id_pais`, `id_dpto`, `id_municipio`, `direccion`
            FROM
                `seg_terceros_noblig`
            WHERE `no_doc` = $doc";
    $rs = $cmd->query($sql);
    $tercero = $rs->fetch();
    if (!empty($tercero)) {
        $res['status'] = '1';
        $res['procedencia'] = $tercero['procedencia'];
        $res['tipo_org'] =  $tercero['tipo_org'];
        $res['reg_fiscal'] =  $tercero['reg_fiscal'];
        $res['resp_fiscal'] =  $tercero['resp_fiscal'];
        $res['id_tdoc'] =   $tercero['id_tdoc'];
        $res['no_doc'] =  $tercero['no_doc'];
        $res['nombre'] =  $tercero['nombre'];
        $res['correo'] = $tercero['correo'];
        $res['telefono'] = $tercero['telefono'];
        $res['id_pais'] = $tercero['id_pais'];
        $res['id_dpto'] = $tercero['id_dpto'];
        $res['id_municipio'] = $tercero['id_municipio'];
        $res['direccion'] = $tercero['direccion'];
    } else {
        $res['status'] = '0';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

echo json_encode($res);
