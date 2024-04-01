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
    $sql = "SELECT id_pgcp,fecha,cuenta,nombre,tipo_dato,nivel,estado FROM seg_ctb_pgcp ORDER BY cuenta ASC;";
    $rs = $cmd->query($sql);
    $lista = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($lista)) {
    foreach ($lista as $lp) {
        $cerrar = null;
        if ((intval($permisos['editar'])) === 1) {
            $id_ctb = $lp['id_pgcp'];
            $editar = '<a id ="editar_' . $id_ctb . '" value="' . $id_ctb . '" onclick="editarDatosPlanCuenta(' . $id_ctb . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb"  title="Editar_' . $id_ctb . '"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            //si es lider de proceso puede abrir o cerrar documentos

        } else {
            $editar = null;
            $detalles = null;
            $acciones = null;
        }
        if ($lp['estado'] == 0) {
            $cerrar = '<a value="' . $id_ctb . '" class="dropdown-item sombra carga" onclick="cerrarCuentaPlan(' . $id_ctb . ')" href="#">Desactivar cuenta</a>';
        } else {
            $cerrar = '<a value="' . $id_ctb . '" class="dropdown-item sombra carga" onclick="abrirCuentaPlan(' . $id_ctb . ')" href="#">Activar cuenta</a>';
        }

        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<a value="' . $id_ctb . '" onclick="eliminarCuentaContable(' . $id_ctb . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            ' . $cerrar . '
            </div>';
        } else {
            $borrar = null;
        }

        if ($lp['estado'] == 0) {
            $estado = '<span class="badge badge-success">Activa</span>';
        } else {
            $estado = '<span class="badge badge-danger">Inactiva</span>';
        }
        $fecha = date("d-m-Y", strtotime($lp['fecha']));
        $data[] = [

            'fecha' => $fecha,
            'cuenta' => $lp['cuenta'],
            'nombre' => $lp['nombre'],
            'tipo' => $lp['tipo_dato'],
            'nivel' => $lp['nivel'],
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
