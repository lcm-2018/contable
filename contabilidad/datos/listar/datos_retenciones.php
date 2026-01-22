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
                `ctb_retenciones`.`id_retencion`
                , `ctb_retencion_tipo`.`tipo`
                , `ctb_retenciones`.`nombre_retencion`
                , `ctb_pgcp`.`cuenta`
                , `ctb_retenciones`.`estado`
            FROM
                `ctb_retenciones`
                INNER JOIN `ctb_retencion_tipo` 
                    ON (`ctb_retenciones`.`id_retencion_tipo` = `ctb_retencion_tipo`.`id_retencion_tipo`)
                LEFT JOIN `ctb_pgcp` 
                    ON (`ctb_retenciones`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)";
    $rs = $cmd->query($sql);
    $retenciones = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($retenciones)) {
    foreach ($retenciones as $lp) {
        $boton = $editar = $borrar = null;
        $id_ret = $lp['id_retencion'];
        $id_r = base64_encode($id_ret);
        $estado = $lp['estado'];
        if ((PermisosUsuario($permisos, 5506, 3) || $id_rol == 1)) {
            $editar = '<a text="' . $id_r . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar"  title="Editar Retención"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $st = base64_encode($id_ret . '|' . $estado);
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
            $borrar = '<a text="' . $id_r . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar"  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }

        if ($estado == 0) {
            $editar =  $borrar = NULL;
        }
        $data[] = [

            'id' => $lp['id_retencion'],
            'tipo' => $lp['tipo'],
            'retencion' => $lp['nombre_retencion'],
            'cuenta' => $lp['cuenta'],
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
