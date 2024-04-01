<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Div de acciones de la lista
$id_ctb_doc = $_POST['id_doc'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_ctb_libaux,id_ctb_doc,cuenta,debito,credito FROM ctb_libaux WHERE id_ctb_doc='$id_ctb_doc' ";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($listappto)) {

    foreach ($listappto as $lp) {
        $id = $lp['id_ctb_libaux'];
        $id_ctb = $lp['id_ctb_doc'];
        // Consultar el nombre de la cuenta contables
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT nombre FROM seg_ctb_pgcp WHERE cuenta='$lp[cuenta]'";
            $rs = $cmd->query($sql);
            $datos = $rs->fetch();
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
        }
        $cuenta = $lp['cuenta'] . ' - ' . $datos['nombre'];
        $valorDebito =  number_format($lp['debito'], 2, '.', ',');
        $valorCredito =  number_format($lp['credito'], 2, '.', ',');

        if ((intval($permisos['editar'])) === 1) {
            $editar = '<a value="' . $id_ctb . '" onclick="editarRegistroDetalle(' . $id . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_ctb . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a value="' . $id_ctb . '" class="dropdown-item sombra carga" href="#">Cargar presupuesto</a>
            <a value="' . $id_ctb . '" class="dropdown-item sombra" href="#">Another action</a>
            <a value="' . $id_ctb . '" class="dropdown-item sombra" href="#">Something else here</a>
            </div>';
            $borrar = '<a value="' . $id_ctb . '" onclick="eliminarRegistroDetalletes(' . $id . ')"class="btn btn-outline-danger btn-sm btn-circle shadow-gb" title="Borrar"><span class="fas fa-trash-alt fa-lg"></span></a>';

            $registrar = '<a value="' . $id_ctb . '" onclick="CargarFormularioCrpp(' . $id_ctb . ')" class="text-blue " role="button" title="Detalles"><span>Registrar</span></a>';
        } else {
            $editar = null;
            $detalles = null;
        }
        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<a value="' . $id_ctb . '" onclick="eliminarRegistroDetalletes(' . $id . ')"class="btn btn-outline-danger btn-sm btn-circle shadow-gb" title="Borrar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        } else {
            // $borrar = null;
        }
        $data[] = [

            'cuenta' => $cuenta,
            'debito' => '<div class="text-right">' . $valorDebito . '</div>',
            'credito' => '<div class="text-right">' . $valorCredito . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . '</div>',

        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
