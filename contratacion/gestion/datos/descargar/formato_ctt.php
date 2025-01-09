<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';

$id_relacion = isset($_POST['id']) ? $_POST['id'] : exit('Acceso no disponible');

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_formatos_doc_rel`.`id_relacion`
                , `ctt_formatos_doc`.`descripcion`
                , `tb_tipo_contratacion`.`tipo_contrato`
            FROM
                `ctt_formatos_doc_rel`
                INNER JOIN `ctt_formatos_doc` 
                    ON (`ctt_formatos_doc_rel`.`id_formato` = `ctt_formatos_doc`.`id_fdoc`)
                INNER JOIN `tb_tipo_contratacion` 
                    ON (`ctt_formatos_doc_rel`.`id_tipo_ctt` = `tb_tipo_contratacion`.`id_tipo`)
            WHERE `ctt_formatos_doc_rel`.`id_relacion` = $id_relacion";
    $rs = $cmd->query($sql);
    $data = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
// ir a '../../../adquisiciones/soportes/$idbs.docx, tomar el formato y descargarlo
if (isset($data)) {
    $file = '../../../adquisiciones/soportes/' . $data['id_relacion'] . '.docx';
    if (file_exists($file)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $data['tipo_contrato'] . ' - ' . $data['descripcion'] . '.docx"');
        readfile($file);
    } else {
        echo 'El archivo no existe';
    }
}
