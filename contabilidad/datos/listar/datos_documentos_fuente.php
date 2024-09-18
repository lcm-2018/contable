<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Div de acciones de la lista
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_doc_fuente`, `cod`, `nombre`, `contab`, `tesor`, `estado`
            FROM
                `ctb_fuente`";
    $rs = $cmd->query($sql);
    $lista = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($lista)) {
    foreach ($lista as $lp) {
        $cerrar = $editar = $borrar = null;
        $id_ctb = $lp['id_doc_fuente'];
        if ((PermisosUsuario($permisos, 5505, 3) || $id_rol == 1) && ($lp['estado'] == 1)) {
            $editar = '<a id ="editar_' . $id_ctb . '" value="' . $id_ctb . '" onclick="editarDocFuente(' . $id_ctb . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb"  title="Editar_' . $id_ctb . '"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5505, 4) || $id_rol == 1) {
            if ($lp['estado'] == 1) {
                $borrar = '<a value="' . $id_ctb . '" onclick="eliminarDocFuente(' . $id_ctb . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
                $cerrar = '<a value="' . $id_ctb . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb" onclick="cerrarFuente(' . $id_ctb . ')" title="Desactivar Fuente"><span class="fas fa-lock-open fa-lg"></span></a>';
            } else {
                $cerrar = '<a value="' . $id_ctb . '" class="btn btn-outline-secondary btn-sm btn-circle shadow-gb" onclick="abrirFuente(' . $id_ctb . ')" title="Activar Fuente"><span class="fas fa-lock fa-lg"></span></a>';
            }
        }
        if ($lp['estado'] == 1) {
            $estado = '<span class="badge badge-success">Activa</span>';
        } else {
            $estado = '<span class="badge badge-danger">Inactiva</span>';
        }
        $data[] = [

            'cod' => $lp['cod'],
            'nombre' => $lp['nombre'],
            'contab' => '<div class="text-center">' . $lp['contab'] . '</div>',
            'tesor' => '<div class="text-center">' . $lp['tesor'] . '</div>',
            'cxpagar' => '',
            'estado' => '<div class="text-center">' . $estado . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . $cerrar . '</div>',
        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
