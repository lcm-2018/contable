<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Div de acciones de la lista
$vigencia =  $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `pto_presupuestos`.`id_pto` AS `id_pto_presupuestos`
                , `pto_presupuestos`.`id_tipo`
                , `pto_tipo`.`nombre` AS `tipo`
                , `pto_presupuestos`.`nombre`
                , `pto_presupuestos`.`descripcion`
                , `tb_vigencias`.`anio` AS `vigencia`
            FROM
                `pto_presupuestos`
                INNER JOIN `pto_tipo` 
                    ON (`pto_presupuestos`.`id_tipo` = `pto_tipo`.`id_tipo`)
                INNER JOIN `tb_vigencias` 
                    ON (`pto_presupuestos`.`id_vigencia` = `tb_vigencias`.`id_vigencia`)
            WHERE (`tb_vigencias`.`anio` = '$vigencia')";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($listappto)) {

    foreach ($listappto as $lp) {
        $id_pto = $lp['id_pto_presupuestos'];
        $detalles = null;
        if (PermisosUsuario($permisos, 5401, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id_pto . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $ejecucion = '<a value="' . $id_pto . '" tipo-id="' . $lp['id_tipo'] . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb ejecucion" title="Ejecucion"><span class="fas fa-tasks fa-lg"></span></a>';
            //$detalles = '<a value="' . $id_pto . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            $acciones = '<button  class="btn btn-outline-secondary btn-sm btn-circle shadow-gb" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false"><i class="fas fa-ellipsis-v fa-lg"></i>
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a value="' . $id_pto . '" class="dropdown-item sombra carga" href="javascript:void(0);">Cargar presupuesto</a>
            <a value="' . $id_pto . '" class="dropdown-item sombra modifica" href="javascript:void(0);">Modificaciones</a>
            <!--<a value="' . $id_pto . '" class="dropdown-item sombra ejecuta" href="javascript:void(0);">Ejecución</a>-->
            <a value="' . $id_pto . '" class="dropdown-item sombra homologa" href="javascript:void(0);">Homologación</a>
            </div>';
        } else {
            $editar = null;
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a value="' . $id_pto . '" class="dropdown-item sombra ejecuta" href="javascript:void(0);">Ejecución</a>
            </div>';
        }
        if (PermisosUsuario($permisos, 5401, 4) || $id_rol == 1) {
            $borrar = '<a value="' . $id_pto . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        } else {
            $borrar = null;
        }

        $data[] = [
            'id_pto' => $lp['id_pto_presupuestos'],
            'nombre' => $lp['nombre'],
            'tipo' => mb_strtoupper($lp['tipo']),
            'vigencia' => $lp['vigencia'],
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . $ejecucion . $detalles . $acciones . '</div>',

        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];


echo json_encode($datos);
