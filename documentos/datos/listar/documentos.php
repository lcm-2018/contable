<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../index.php');
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';

$vigencia = $_SESSION['vigencia'];
$id_user = $_SESSION['id_user'];
$modulos = [];
foreach ($perm_modulos as $mod) {
    $modulos[] = $mod['id_modulo'];
}
$ids = implode(',', $modulos);
$where = '';
if ($id_rol != 1) {
    $where = " AND `fin_maestro_doc`.`id_modulo` IN ($ids)";
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `fin_maestro_doc`.`id_maestro`
                , `fin_maestro_doc`.`fecha_doc`
                , `fin_maestro_doc`.`control_doc`
                , `fin_maestro_doc`.`version_doc`
                , `fin_maestro_doc`.`estado`
                , `seg_modulos`.`nom_modulo`
                , `ctb_fuente`.`nombre`
            FROM
                `fin_maestro_doc`
                INNER JOIN `seg_modulos` 
                    ON (`fin_maestro_doc`.`id_modulo` = `seg_modulos`.`id_modulo`)
                INNER JOIN `ctb_fuente` 
                    ON (`fin_maestro_doc`.`id_doc_fte` = `ctb_fuente`.`id_doc_fuente`)
            WHERE '1' = '1' $where
            ORDER BY `seg_modulos`.`nom_modulo` ASC";
    $rs = $cmd->query($sql);
    $documentos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($documentos)) {
    foreach ($documentos as $doc) {
        $editar = $detalles = $borrar = NULL;
        $id_doc = $doc['id_maestro'];
        $id = base64_encode($id_doc);
        $det = base64_encode($id_doc . '|0');
        if ($id_rol == 1 || PermisosUsuario($permisos, 6001, 1)) {
            $detalles = '<a text="' . $det . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles Documento"><span class="far fa-eye fa-lg"></span></a>';
        }
        if ($id_rol == 1 || PermisosUsuario($permisos, 6001, 3)) {
            $editar = '<a text="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if ($id_rol == 1 || PermisosUsuario($permisos, 6001, 4)) {
            $borrar = '<a text="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $estado = $doc['estado'];
        $st = base64_encode($id_doc . '|' . $estado);
        if ($estado == 1) {
            $title = 'Activo';
            $icono = 'on';
            $color = '#37E146';
        } else {
            $title = 'Inactivo';
            $icono = 'off';
            $color = 'gray';
        }
        if ($estado == 0) {
            $editar =  $borrar = $detalles = NULL;
        }
        if ($id_rol == 1 || PermisosUsuario($permisos, 6001, 3)) {
            $boton = '<a text="' . $st . '" class="btn btn-sm btn-circle estado" title="' . $title . '"><span class="fas fa-toggle-' . $icono . ' fa-2x" style="color:' . $color . ';"></span></a>';
        }
        
        $data[] = [
            'id' => $doc['id_maestro'],
            'modulo' => mb_strtoupper($doc['nom_modulo']),
            'doc' => mb_strtoupper($doc['nombre']),
            'fecha' => $doc['fecha_doc'] != '' ? date('Y-m-d', strtotime($doc['fecha_doc'])) : '',
            'control' => mb_strtoupper($doc['control_doc'] == 0 ? 'NO' : 'SI'),
            'version' => $doc['version_doc'],
            'estado' => '<div class="text-center">' . $boton . '</div>',
            'botones' => '<div class="text-center">' . $editar . $detalles . $borrar . '</div>',
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);
