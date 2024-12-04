<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include_once '../../../conexion.php';
include_once '../../../permisos.php';

// Div de acciones de la lista

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_retencion_tipo`.`id_retencion_tipo`
                , `ctb_retencion_tipo`.`tipo`
                , `ctb_retencion_tipo`.`id_tercero`
                , `ctb_retencion_tipo`.`estado`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
            FROM
                `ctb_retencion_tipo`
                LEFT JOIN `tb_terceros` 
                    ON (`ctb_retencion_tipo`.`id_tercero` = `tb_terceros`.`id_tercero_api`)";
    $rs = $cmd->query($sql);
    $tipos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($tipos)) {
    foreach ($tipos as $lp) {
        $boton = $editar = $borrar = null;
        $id_tipo = $lp['id_retencion_tipo'];
        $id_tp = base64_encode($id_tipo);
        $estado = $lp['estado'];
        if ((PermisosUsuario($permisos, 5506, 3) || $id_rol == 1)) {
            $editar = '<a text="' . $id_tp . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar"  title="Editar Tipo Retención"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $st = base64_encode($id_tipo . '|' . $estado);
            if ($estado == '1') {
                $title = 'Activo';
                $icono = 'on';
                $color = '#37E146';
            } else {
                $title = 'Inactivo';
                $icono = 'off';
                $color = 'gray';
            }
            $boton = '<a text="' . $st . '" class="btn btn-sm btn-circle estado" title="' . $title . '"><span class="fas fa-toggle-' . $icono . ' fa-2x" style="color:' . $color . ';"></span></a>';
        }
        if (PermisosUsuario($permisos, 5506, 4) || $id_rol == 1) {
            $borrar = '<a text="' . $id_tp . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar"  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }

        if ($estado == 0) {
            $editar =  $borrar = NULL;
        }
        $data[] = [

            'id' => $lp['id_retencion_tipo'],
            'tipo' => $lp['tipo'],
            'tercero' => $lp['nom_tercero'],
            'estado' => '<div class="text-center">' . $boton . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . '</div>',
        ];
    }
} else {
    $data = [];
}
$datos = [
    'data' => $data
];


echo json_encode($datos);
