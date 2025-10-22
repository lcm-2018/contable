<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
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

$where = "WHERE id_tipo_ingreso<>0";
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND nom_tipo_ingreso LIKE '" . $_POST['nombre'] . "%'";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_orden_ingreso_tipo WHERE id_tipo_ingreso<>0";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM far_orden_ingreso_tipo $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT id_tipo_ingreso,nom_tipo_ingreso,
                IF(es_int_ext=1,'Interno','Externo') AS es_int_ext,
                IF(orden_compra=1,'SI','') AS orden_compra,
                IF(fianza=1,'SI','') AS fianza,
                IF(farmacia=1,'SI','') AS farmacia,
                IF(almacen=1,'SI','') AS almacen,
                IF(activofijo=1,'SI','') AS activofijo
            FROM far_orden_ingreso_tipo
            $where ORDER BY $col $dir $limit";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$editar = NULL;
$eliminar = NULL;
$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id = $obj['id_tipo_ingreso'];
        /*Permisos del usuario
            5512-Opcion [General][tipos_orden_ingreso]
            1-Consultar, 2-Adicionar, 3-Modificar, 4-Eliminar, 5-Anular, 6-Imprimir
        */    
        if (PermisosUsuario($permisos, 5512, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5512, 4) || $id_rol == 1) {
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }

        $data[] = [
            "id_tipo_ingreso" => $id,
            "nom_tipo_ingreso" => mb_strtoupper($obj['nom_tipo_ingreso']),
            "es_int_ext" => $obj['es_int_ext'],
            "orden_compra" => $obj['orden_compra'],
            "fianza" => $obj['fianza'],
            "almacen" => $obj['almacen'],
            "farmacia" => $obj['farmacia'],            
            "activofijo" => $obj['activofijo'],
            "botones" => '<div class="text-center centro-vertical">' . $editar . $eliminar . '</div>',
        ];
    }
}
$cmd = null;
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
