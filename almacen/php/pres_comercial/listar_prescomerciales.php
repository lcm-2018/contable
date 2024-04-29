<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';

$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$limit = "";
if ($length != -1){
    $limit = "LIMIT $start, $length";
}
$col = $_POST['order'][0]['column']+1;
$dir = $_POST['order'][0]['dir'];

$where = "WHERE far_presentacion_comercial.id_prescom<>0";
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND far_presentacion_comercial.nom_presentacion LIKE '" . $_POST['nombre'] . "%'";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_presentacion_comercial WHERE id_prescom<>0";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM far_presentacion_comercial $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT id_prescom,nom_presentacion,cantidad
            FROM far_presentacion_comercial
            $where ORDER BY $col $dir $limit";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$editar = NULL;
$eliminar = NULL;
$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id = $obj['id_prescom'];
        /*Permisos del usuario
            5016-Opcion [Presentacion Comercial]
            1-Consultar, 2-Adicionar, 3-Modificar, 4-Eliminar, 5-Anular, 6-Imprimir
        */    
        if (PermisosUsuario($permisos, 5016, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5016, 4) || $id_rol == 1) {
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id_prescom" => $id,
            "nom_presentacion" => mb_strtoupper($obj['nom_presentacion']),
            "cantidad" => $obj['cantidad'],
            "botones" => '<div class="text-center centro-vertical">' . $editar . $eliminar . '</div>',
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
