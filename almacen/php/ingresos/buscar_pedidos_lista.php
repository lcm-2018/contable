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

$id_bodega = $_POST['id_bodega'];

$where = "WHERE 1=1";
if (isset($_POST['num_pedido']) && $_POST['num_pedido']) {
    $where .= " AND num_pedido='" . $_POST['num_pedido'] . "'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND fec_pedido BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $sqlc= "SELECT DISTINCT far_alm_pedido.id_pedido,far_alm_pedido.num_pedido,far_alm_pedido.detalle,far_alm_pedido.fec_pedido
            FROM far_alm_pedido_detalle
            INNER JOIN far_alm_pedido ON (far_alm_pedido.id_pedido=far_alm_pedido_detalle.id_pedido)
            LEFT JOIN (
                    SELECT far_orden_ingreso.id_pedido,far_medicamento_lote.id_med,SUM(far_orden_ingreso_detalle.cantidad*far_presentacion_comercial.cantidad) AS cantidad_ing
                    FROM far_orden_ingreso_detalle
                    INNER JOIN far_orden_ingreso ON (far_orden_ingreso.id_ingreso=far_orden_ingreso_detalle.id_ingreso)
                    INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote=far_orden_ingreso_detalle.id_lote)
                    INNER JOIN far_presentacion_comercial ON (far_presentacion_comercial.id_prescom=far_orden_ingreso_detalle.id_presentacion)
                    WHERE far_orden_ingreso.id_pedido IS NOT NULL AND far_orden_ingreso.estado<>0 AND far_orden_ingreso.id_bodega=$id_bodega
                    GROUP BY far_orden_ingreso.id_pedido,far_medicamento_lote.id_med
                ) AS ing ON (ing.id_pedido=far_alm_pedido.id_pedido AND ing.id_med=far_alm_pedido_detalle.id_medicamento)
            WHERE far_alm_pedido.tipo=1 AND far_alm_pedido.estado=3 AND far_alm_pedido.id_bodega=$id_bodega AND IF(ing.cantidad_ing IS NULL,0,ing.cantidad_ing)<far_alm_pedido_detalle.aprobado";
           
    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM ($sqlc) AS c";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM ($sqlc) AS c ". $where;
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT id_pedido,num_pedido,fec_pedido,detalle FROM ($sqlc) AS c "
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

   