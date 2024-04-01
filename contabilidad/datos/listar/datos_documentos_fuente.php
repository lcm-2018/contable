<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Div de acciones de la lista
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_doc_fuente,cod,nombre,contab,tesor,cxpagar,estado FROM ctb_fuente;";
    $rs = $cmd->query($sql);
    $lista = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($lista)) {
    foreach ($lista as $lp) {
        $cerrar = null;
        if ((intval($permisos['editar'])) === 1) {
            $id_ctb = $lp['id_doc_fuente'];
            $editar = '<a id ="editar_' . $id_ctb . '" value="' . $id_ctb . '" onclick="editarDatosCuenta(' . $id_ctb . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb"  title="Editar_' . $id_ctb . '"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_ctb . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            //si es lider de proceso puede abrir o cerrar documentos

        } else {
            $editar = null;
            $detalles = null;
            $acciones = null;
        }
        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<a value="' . $id_ctb . '" onclick="eliminarCuentaBancaria(' . $id_ctb . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            ' . $cerrar . '
            </div>';
        } else {
            $borrar = null;
        }
        if ($lp['estado'] == 1) {
            $estado = '<span class="badge badge-success">Activa</span>';
        } else {
            $estado = '<span class="badge badge-danger">Inactiva</span>';
        }
        $data[] = [

            'cod' => $lp['cod'],
            'nombre' => $lp['nombre'],
            'contab' => $lp['contab'],
            'tesor' => $lp['tesor'],
            'cxpagar' => $lp['cxpagar'],
            'estado' => $estado,
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar  . $acciones . '</div>',
        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
