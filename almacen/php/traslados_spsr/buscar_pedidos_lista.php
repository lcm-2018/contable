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

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $bodega = bodega_principal($cmd);
    $id_bodega_origen = $bodega['id_bodega'] ? $bodega['id_bodega'] : 0;

    $where_usr = " WHERE PP.estado=2 AND PP.id_bodega_origen=$id_bodega_origen";

    if (isset($_POST['ped_parcial']) && $_POST['ped_parcial']) {
        $where_usr.= " AND PP.id_pedido NOT IN 
                        (SELECT far_pedido_detalle.id_pedido
                        FROM far_pedido_detalle
                        LEFT JOIN (SELECT TRD.id_ped_detalle,SUM(TRD.cantidad) AS cantidad     
                                FROM far_traslado_r_detalle AS TRD
                                INNER JOIN far_traslado_r AS TR ON (TR.id_traslado=TRD.id_traslado)
                                WHERE TR.estado<>0 AND TRD.id_ped_detalle IS NOT NULL
                                GROUP BY TRD.id_ped_detalle
                            ) AS TRASLADO ON (TRASLADO.id_ped_detalle=far_pedido_detalle.id_ped_detalle)
                        GROUP BY far_pedido_detalle.id_pedido
                        HAVING SUM(IF(far_pedido_detalle.cantidad>IFNULL(TRASLADO.cantidad,0),1,0))=0)";    
    } else {
        $where_usr.= " AND PP.id_pedido NOT IN 
                        (SELECT PD.id_pedido FROM far_pedido_detalle AS PD 
                        INNER JOIN far_traslado_r_detalle AS TD ON (TD.id_ped_detalle=PD.id_ped_detalle)
                        INNER JOIN far_traslado_r AS TT ON (TT.id_traslado=TD.id_traslado)
                        WHERE TT.estado<>0)";
    }

    if($idrol !=1){
        $where_usr .= " AND PP.id_bodega_origen IN (SELECT id_bodega FROM seg_bodegas_usuario WHERE id_usuario=$idusr)";
    }

    $where = $where_usr;
    if (isset($_POST['num_pedido']) && $_POST['num_pedido']) {
        $where .= " AND PP.num_pedido='" . $_POST['num_pedido'] . "'";
    }
    if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
        $where .= " AND PP.fec_pedido BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
    }
       
    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_pedido AS PP" . $where_usr;
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM far_pedido AS PP" . $where;
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT PP.*,
                SS.nom_sede AS nom_sede_solicita,BS.nombre AS nom_bodega_solicita                
            FROM far_pedido AS PP
            INNER JOIN tb_sedes AS SS ON (SS.id_sede = PP.id_sede_destino)
            INNER JOIN far_bodegas AS BS ON (BS.id_bodega = PP.id_bodega_destino)"
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
            "id_sede_destino" => $obj['id_sede_destino'],
            "nom_sede_solicita" => $obj['nom_sede_solicita'],
            "id_bodega_destino" => $obj['id_bodega_destino'],
            "nom_bodega_solicita" => $obj['nom_bodega_solicita'],
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

   