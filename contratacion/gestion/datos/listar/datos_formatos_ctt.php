<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_formatos_doc_rel`.`id_relacion`
                , `ctt_formatos_doc`.`descripcion`
                , `tb_tipo_bien_servicio`.`tipo_bn_sv`
                , `tb_tipo_contratacion`.`tipo_contrato`
            FROM
                `ctt_formatos_doc_rel`
                INNER JOIN `ctt_formatos_doc` 
                    ON (`ctt_formatos_doc_rel`.`id_formato` = `ctt_formatos_doc`.`id_fdoc`)
                INNER JOIN `tb_tipo_bien_servicio` 
                    ON (`ctt_formatos_doc_rel`.`id_tipo_bn_sv` = `tb_tipo_bien_servicio`.`id_tipo_b_s`)
                INNER JOIN `tb_tipo_contratacion` 
                    ON (`tb_tipo_bien_servicio`.`id_tipo_cotrato` = `tb_tipo_contratacion`.`id_tipo`)";
    $rs = $cmd->query($sql);
    $formatos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($formatos)) {
    foreach ($formatos as $form) {
        $id_form = $form['id_relacion'];
        $borrar = $descargar = null;

        if (PermisosUsuario($permisos, 5301, 4) || $id_rol == 1) {
            $borrar = '<a value="' . $id_form . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5301, 3) || $id_rol == 1) {
            $descargar = '<a value="' . $id_form . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb descargar" title="Descargar formato"><span class="fas fa-download fa-lg"></span></a>';
        }
        $data[] = [
            'id' => $id_form,
            'formato' => $form['descripcion'],
            'tp_ctt' => $form['tipo_contrato'] . ' -> ' . $form['tipo_bn_sv'],
            'botones' => '<div class="text-center">' . $descargar . $borrar . '</div>',
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);
