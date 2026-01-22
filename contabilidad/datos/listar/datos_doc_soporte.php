<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include_once '../../../conexion.php';
include_once '../../../permisos.php';

function pesos($valor)
{
    return '$ ' . number_format($valor, 0, '', '.');
}

$id_vigencia = $_SESSION['id_vigencia'];
/*
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "";
    $rs = $cmd->query($sql);
    $documentos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}*/
$documentos = [];
$data = [];
if (!empty($documentos)) {
    foreach ($documentos as  $doc) {
        $boton = $editar = $borrar = null;
        if ((PermisosUsuario($permisos, 5510, 3) || $id_rol == 1)) {
            $editar = '<a text="' . $id_r . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar"  title="Editar Retención"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $st = base64_encode($id_rango . '|' . $estado);
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

            'id' => $lp['id_rango'],
            'ref' => $lp['tipo'],
            'inicia' => $lp['nombre_retencion'],
            'vence' => '<div class="text-right">' . pesos($lp['valor_base']) . '</div>',
            'tipo_doc' => '<div class="text-right">' . pesos($lp['valor_tope']) . '</div>',
            'num_doc' => $lp['tarifa'],
            'nombre' => '<div class="text-center">' . $boton . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . '</div>',
        ];
    }
}
$datos = [
    'data' => $data
];


echo json_encode($datos);
