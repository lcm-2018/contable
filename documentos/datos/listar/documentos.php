<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../index.php');
    exit();
}
include '../../../conexion.php';

$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `fin_maestro_doc`.`id_maestro`
                ,`fin_maestro_doc`.`fecha_doc`
                , `seg_modulos`.`nom_modulo`
                , `ctb_fuente`.`nombre`
                , `t1`.`nom_tercero`
                , `t1`.`descripcion` AS `control`
                , `t1`.`fecha_ini`
                , `t1`.`fecha_fin`
                , `t1`.`id_respon_doc`
            FROM
                `fin_maestro_doc`
                INNER JOIN `seg_modulos` 
                    ON (`fin_maestro_doc`.`id_modulo` = `seg_modulos`.`id_modulo`)
                INNER JOIN `ctb_fuente` 
                    ON (`fin_maestro_doc`.`id_doc_fte` = `ctb_fuente`.`id_doc_fuente`)
                LEFT JOIN 
                (SELECT 
                    `fin_respon_doc`.`id_maestro_doc`
                    , `fin_respon_doc`.`id_respon_doc`
                    , `tb_terceros`.`nom_tercero`
                    , `fin_respon_doc`.`tipo_control`
                    , `fin_tipo_control`.`descripcion`
                    , `fin_respon_doc`.`fecha_ini`
                    , `fin_respon_doc`.`fecha_fin`
                FROM `fin_respon_doc`
                LEFT JOIN `tb_terceros` 
                    ON (`fin_respon_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                LEFT JOIN `fin_tipo_control` 
                    ON (`fin_respon_doc`.`tipo_control` = `fin_tipo_control`.`id_tipo`)
                WHERE `fin_respon_doc`.`fecha_ini` BETWEEN '{$vigencia}-01-01' AND '{$vigencia}-12-31' OR `fin_respon_doc`.`fecha_fin` BETWEEN '{$vigencia}-01-01' AND '{$vigencia}-12-31') AS `t1`
                ON(`fin_maestro_doc`.`id_maestro` = `t1`.`id_maestro_doc`)
            ORDER BY `seg_modulos`.`nom_modulo`,`t1`.`fecha_ini`, `t1`.`fecha_fin` ASC";
    $rs = $cmd->query($sql);
    $documentos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($documentos)) {
    foreach ($documentos as $doc) {
        $id_doc = $doc['id_maestro'];
        $id_resp = $doc['id_respon_doc'];
        $ids = base64_encode($id_doc . '|' . $id_resp);
        $editar = '<a text="' . $ids . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        $borrar = '<a text="' . $ids . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        $data[] = [
            'id' => $doc['id_maestro'],
            'modulo' => mb_strtoupper($doc['nom_modulo']),
            'doc' => mb_strtoupper($doc['nombre']),
            'fecha' => $doc['fecha_doc'] != '' ? date('Y-m-d', strtotime($doc['fecha_doc'])) : '',
            'resp' => mb_strtoupper($doc['nom_tercero']),
            'control' => mb_strtoupper($doc['control']),
            'inicio' => $doc['fecha_ini'] != '' ? date('Y-m-d', strtotime($doc['fecha_ini'])) : '',
            'fin' => $doc['fecha_fin'] != '' ? date('Y-m-d', strtotime($doc['fecha_fin'])) : '',
            'botones' => '<div class="text-center">' . $editar . $borrar . '</div>',
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);
