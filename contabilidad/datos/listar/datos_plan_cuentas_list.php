<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include_once '../../../conexion.php';
include_once '../../../permisos.php';
include_once '../../../financiero/consultas.php';

// Div de acciones de la lista
$vigencia = $_SESSION['vigencia'];
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$where = $_POST['search']['value'] != '' ? "WHERE (`cuenta` LIKE '%{$_POST['search']['value']}%' OR `nombre` LIKE '%{$_POST['search']['value']}%')" : '';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_pgcp`, `fecha`, `cuenta`, `nombre`, `tipo_dato`, `estado`
            FROM`ctb_pgcp`
            $where
            ORDER BY `cuenta` ASC
            LIMIT $start, $length";
    $rs = $cmd->query($sql);
    $lista = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT COUNT(*) AS `total` FROM `ctb_pgcp` $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($lista)) {
    foreach ($lista as $lp) {
        $cerrar = $editar = $detalles = $borrar = null;
        $id_ctb = $lp['id_pgcp'];
        if ((PermisosUsuario($permisos, 5504, 3) || $id_rol == 1) && ($lp['estado'] == 1)) {
            $editar = '<a id ="editar_' . $id_ctb . '" value="' . $id_ctb . '" onclick="editarDatosPlanCuenta(' . $id_ctb . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb"  title="Editar_' . $id_ctb . '"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5504, 4) || $id_rol == 1) {
            if ($lp['estado'] == 1) {
                $cerrar = '<a value="' . $id_ctb . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb" onclick="cerrarCuentaPlan(' . $id_ctb . ')" title="Desactivar cuenta"><span class="fas fa-lock-open fa-lg"></span></a>';
                $borrar = '<a value="' . $id_ctb . '" onclick="eliminarCuentaContable(' . $id_ctb . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            } else {
                $cerrar = '<a value="' . $id_ctb . '" class="btn btn-outline-secondary btn-sm btn-circle shadow-gb" onclick="abrirCuentaPlan(' . $id_ctb . ')" title="Activar cuenta"><span class="fas fa-lock fa-lg"></span></a>';
            }
        }
        if ($lp['estado'] == 1) {
            $estado = '<span class="badge badge-success">Activa</span>';
        } else {
            $estado = '<span class="badge badge-danger">Inactiva</span>';
        }
        $fecha = date("d-m-Y", strtotime($lp['fecha']));
        $data[] = [

            'fecha' => $fecha,
            'cuenta' => $lp['cuenta'],
            'nombre' => $lp['nombre'],
            'tipo' => '<div class="text-center">' . $lp['tipo_dato'] . '</div>',
            'nivel' => '<div class="text-center">' . Nivel($lp['cuenta']) . '</div>',
            'estado' => '<div class="text-center">' . $estado . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . $cerrar. '</div>',
        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = [
    'data' => $data,
    'recordsFiltered' => $totalRecords,
    'recordsTotal' => $totalRecords,
];


echo json_encode($datos);
