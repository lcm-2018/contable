<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
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
                `tb_terceros`.`nit_tercero`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`id_municipio`
                , `tb_terceros`.`tipo_doc`
                , `tb_departamentos`.`id_departamento`
                , `tb_terceros`.`dir_tercero`
                , `tb_terceros`.`tel_tercero`
                , `tb_terceros`.`id_tercero_api`
                , `tb_terceros`.`email`
                , `tb_terceros`.`procedencia`
                , `tb_terceros`.`tipo_org`
                , `tb_terceros`.`reg_fiscal`
                , `tb_terceros`.`resp_fiscal`
            FROM
                `tb_terceros`
                LEFT JOIN `tb_municipios` 
                    ON (`tb_terceros`.`id_municipio` = `tb_municipios`.`id_municipio`)
                LEFT JOIN `tb_departamentos` 
                    ON (`tb_municipios`.`id_departamento` = `tb_departamentos`.`id_departamento`)
                LEFT JOIN `tb_tipos_documento` 
                    ON (`tb_terceros`.`tipo_doc` = `tb_tipos_documento`.`id_tipodoc`)
            WHERE (`tb_terceros`.`nit_tercero` = '$doc')";
    $rs = $cmd->query($sql);
    $tercero = $rs->fetch();
    if (!empty($tercero)) {
        $res['status'] = 1;
        $res['procedencia'] = $tercero['procedencia'] == '' ? 0 : $tercero['procedencia'];
        $res['tipo_org'] = $tercero['tipo_org'] == '' ? 0 : $tercero['tipo_org'];
        $res['reg_fiscal'] = $tercero['reg_fiscal'] == '' ? 0 : $tercero['reg_fiscal'];
        $res['resp_fiscal'] = $tercero['resp_fiscal']   == '' ? 0 : $tercero['resp_fiscal'];
        $res['id_tdoc'] =   $tercero['tipo_doc'];
        $res['nombre'] =  $tercero['nom_tercero'];
        $res['correo'] = $tercero['email'];
        $res['telefono'] = $tercero['tel_tercero'];
        $res['id_pais'] = 27;
        $res['id_dpto'] = $tercero['id_departamento'] == '' ? 0 : $tercero['id_departamento'];
        $res['id_municipio'] = $tercero['id_municipio'] == '' ? 0 : $tercero['id_municipio'];
        $res['id_tercero_api'] = $tercero['id_tercero_api'];
        $res['direccion'] = $tercero['dir_tercero'];
    } else {
        $res['status'] = '0';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

echo json_encode($res);
