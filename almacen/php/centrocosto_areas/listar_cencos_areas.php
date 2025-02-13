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

$where = "WHERE far_centrocosto_area.id_area<>0";
if (isset($_POST['nom_area']) && $_POST['nom_area']) {
    $where .= " AND far_centrocosto_area.nom_area LIKE '" . $_POST['nom_area'] . "%'";
}
if (isset($_POST['id_cencosto']) && $_POST['id_cencosto']) {
    $where .= " AND far_centrocosto_area.id_centrocosto=" . $_POST['id_cencosto'];
}
if (isset($_POST['id_sede']) && $_POST['id_sede']) {
    $where .= " AND far_centrocosto_area.id_sede=" . $_POST['id_sede'];
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_centrocosto_area WHERE id_area<>0";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM far_centrocosto_area $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT far_centrocosto_area.id_area,far_centrocosto_area.nom_area, 
                tb_centrocostos.nom_centro AS nom_centrocosto, 
                far_area_tipo.nom_tipo AS nom_tipo_area,              
                CONCAT_WS(' ',usr.nombre1,usr.nombre2,usr.apellido1,usr.apellido2) AS usr_responsable,
                tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega
            FROM far_centrocosto_area    
            INNER JOIN tb_centrocostos ON (tb_centrocostos.id_centro=far_centrocosto_area.id_centrocosto)
            INNER JOIN far_area_tipo ON (far_area_tipo.id_tipo=far_centrocosto_area.id_tipo_area)
            INNER JOIN seg_usuarios_sistema AS usr ON (usr.id_usuario=far_centrocosto_area.id_responsable)
            INNER JOIN tb_sedes ON (tb_sedes.id_sede=far_centrocosto_area.id_sede)
            LEFT JOIN far_bodegas ON (far_bodegas.id_bodega=far_centrocosto_area.id_bodega)
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
        $id = $obj['id_area'];
        /*Permisos del usuario
           5015-Opcion [General][Centros Costo-Areas]
            1-Consultar, 2-Adicionar, 3-Modificar, 4-Eliminar, 5-Anular, 6-Imprimir
        */    
        if (PermisosUsuario($permisos, 5015, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5015, 4) || $id_rol == 1) {
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id_area" => $id,          
            "nom_area" => mb_strtoupper($obj['nom_area']),             
            "nom_tipo_area" => mb_strtoupper($obj['nom_tipo_area']),             
            "nom_centrocosto" => mb_strtoupper($obj['nom_centrocosto']), 
            "nom_sede" => mb_strtoupper($obj['nom_sede']), 
            "usr_responsable" => mb_strtoupper($obj['usr_responsable']), 
            "nom_bodega" => mb_strtoupper($obj['nom_bodega']), 
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
