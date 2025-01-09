<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
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

$where_usr = " WHERE PP.estado=2";
$where_usr.= " AND PP.id_pedido NOT IN 
                    (SELECT PD.id_pedido FROM far_cec_pedido_detalle AS PD 
                    INNER JOIN far_orden_egreso_detalle AS ED ON (ED.id_ped_detalle=PD.id_ped_detalle)
                    INNER JOIN far_orden_egreso AS EE ON (EE.id_egreso=ED.id_egreso)
                    WHERE EE.estado<>0)";
if($idrol !=1){
    $where_usr .= " AND PP.id_bodega IN (SELECT id_bodega FROM seg_bodegas_usuario WHERE id_usuario=$idusr)";
}

$where = $where_usr;
if (isset($_POST['num_pedido']) && $_POST['num_pedido']) {
    $where .= " AND PP.num_pedido='" . $_POST['num_pedido'] . "'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND PP.fec_pedido BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
       
    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_cec_pedido AS PP" . $where_usr;
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM far_cec_pedido AS PP" . $where;
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT PP.*,
                CC.nom_centro,SE.nom_sede,BO.nombre AS nom_bodega
            FROM far_cec_pedido AS PP
            INNER JOIN tb_centrocostos AS CC ON (CC.id_centro = PP.id_cencosto)      
            INNER JOIN tb_sedes AS SE ON (SE.id_sede = PP.id_sede)
            INNER JOIN far_bodegas AS BO ON (BO.id_bodega = PP.id_bodega)" 
            . $where . " ORDER BY $col $dir $limit";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id = $obj['id_pedido'];
        $imprimir =  '<a value="' . $id . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb btn_imprimir" title="imprimir"><span class="fas fa-print fa-lg"></span></a>';
        $data[] = [
            "id_pedido" => $id,
            "num_pedido" => $obj['num_pedido'],
            "fec_pedido" => $obj['fec_pedido'],
            "detalle" => $obj['detalle'],
            "id_cencosto" => $obj['id_cencosto'],
            "nom_centro" => $obj['nom_centro'],
            "id_sede" => $obj['id_sede'],
            "nom_sede" => $obj['nom_sede'],
            "id_bodega" => $obj['id_bodega'],
            "nom_bodega" => $obj['nom_bodega'],
            "botones" => '<div class="text-center centro-vertical">' . $imprimir . '</div>'
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);

   