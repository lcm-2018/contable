<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
include '../common/funciones_generales.php';

$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$limit = "";
if ($length != -1) {
    $limit = "LIMIT $start, $length";
}
$col = $_POST['order'][0]['column'] + 1;
$dir = $_POST['order'][0]['dir'];
$idusr = $_SESSION['id_user'];
$idrol = $_SESSION['rol'];

$where_usr = " WHERE 1";
if($idrol !=1){
    $where_usr .= " AND far_traslado.id_bodega_origen IN (SELECT id_bodega FROM seg_bodegas_usuario WHERE id_usuario=$idusr)";
}
$where = "";
if (isset($_POST['id_sedori']) && $_POST['id_sedori']) {
    $where .= " AND far_traslado.id_sede_origen='" . $_POST['id_sedori'] . "'";
}
if (isset($_POST['id_bodori']) && $_POST['id_bodori']) {
    $where .= " AND far_traslado.id_bodega_origen='" . $_POST['id_bodori'] . "'";
}
if (isset($_POST['id_tra']) && $_POST['id_tra']) {
    $where .= " AND far_traslado.id_traslado='" . $_POST['id_tra'] . "'";
}
if (isset($_POST['num_tra']) && $_POST['num_tra']) {
    $where .= " AND far_traslado.num_traslado='" . $_POST['num_tra'] . "'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND far_traslado.fec_traslado BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['id_seddes']) && $_POST['id_seddes']) {
    $where .= " AND far_traslado.id_sede_destino='" . $_POST['id_seddes'] . "'";
}
if (isset($_POST['id_boddes']) && $_POST['id_boddes']) {
    $where .= " AND far_traslado.id_bodega_destino='" . $_POST['id_boddes'] . "'";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND far_traslado.estado=" . $_POST['estado'];
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_traslado $where_usr";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM far_traslado $where_usr $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT far_traslado.id_traslado,far_traslado.num_traslado,far_traslado.fec_traslado,far_traslado.hor_traslado,
                far_traslado.detalle,far_traslado.val_total,
                tb_so.nom_sede AS nom_sede_origen,tb_bo.nombre AS nom_bodega_origen,
                tb_sd.nom_sede AS nom_sede_destino,tb_bd.nombre AS nom_bodega_destino,
                CASE far_traslado.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' WHEN 0 THEN 'ANULADO' END AS nom_estado
            FROM far_traslado
            INNER JOIN tb_sedes AS tb_so ON (tb_so.id_sede=far_traslado.id_sede_origen)
            INNER JOIN far_bodegas AS tb_bo ON (tb_bo.id_bodega=far_traslado.id_bodega_origen)
            INNER JOIN tb_sedes AS tb_sd ON (tb_sd.id_sede=far_traslado.id_sede_destino)
            INNER JOIN far_bodegas AS tb_bd ON (tb_bd.id_bodega=far_traslado.id_bodega_destino)
            $where_usr $where ORDER BY $col $dir $limit";

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
        $id = $obj['id_traslado'];
        //Permite crear botones en la cuadricula si tiene permisos de 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
        if (PermisosUsuario($permisos, 5008, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5008, 4) || $id_rol == 1) {
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id_traslado" => $id,
            "num_traslado" => $obj['num_traslado'],
            "fec_traslado" => $obj['fec_traslado'],
            "hor_traslado" => $obj['hor_traslado'],
            "detalle" => $obj['detalle'],
            "nom_sede_origen" => mb_strtoupper($obj['nom_sede_origen']),
            "nom_bodega_origen" => mb_strtoupper($obj['nom_bodega_origen']),
            "nom_sede_destino" => mb_strtoupper($obj['nom_sede_destino']),
            "nom_bodega_destino" => mb_strtoupper($obj['nom_bodega_destino']),
            "val_total" => formato_valor($obj['val_total']),            
            "nom_estado" => $obj['nom_estado'],
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
