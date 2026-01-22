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
$id_pedido = $_POST['id_pedido'];

$where = " WHERE 1=1";
if (isset($_POST['codigo']) && $_POST['codigo']) {
    $where .= " AND cod_medicamento LIKE '" . $_POST['codigo'] . "%'";
}
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND nom_medicamento LIKE '" . $_POST['nombre'] . "%'";
}
if (isset($_POST['can_pen']) && $_POST['can_pen']) {
    $where .= " AND cantidad_ing<cantidad_ord";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        
    $sqlc= "SELECT far_alm_pedido_detalle.id_ped_detalle,
                    far_medicamentos.id_med,far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,
	                far_alm_pedido_detalle.aprobado AS cantidad_ord,
	                IF(ing.cantidad_ing IS NULL,0,ing.cantidad_ing) AS cantidad_ing,
                    (far_alm_pedido_detalle.aprobado-IF(ing.cantidad_ing IS NULL,0,ing.cantidad_ing)) AS cantidad_pen
            FROM far_alm_pedido_detalle
            INNER JOIN far_alm_pedido ON (far_alm_pedido.id_pedido=far_alm_pedido_detalle.id_pedido)
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_alm_pedido_detalle.id_medicamento)
            LEFT JOIN (SELECT far_medicamento_lote.id_med,SUM(far_orden_ingreso_detalle.cantidad*far_presentacion_comercial.cantidad) AS cantidad_ing
                FROM far_orden_ingreso_detalle
                INNER JOIN far_orden_ingreso ON (far_orden_ingreso.id_ingreso=far_orden_ingreso_detalle.id_ingreso)
                INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote=far_orden_ingreso_detalle.id_lote)
                INNER JOIN far_presentacion_comercial ON (far_presentacion_comercial.id_prescom=far_orden_ingreso_detalle.id_presentacion)
                WHERE far_orden_ingreso.id_pedido=$id_pedido AND far_orden_ingreso.estado<>0 AND far_orden_ingreso.id_bodega=$id_bodega
                GROUP BY far_medicamento_lote.id_med
            ) AS ing ON (ing.id_med=far_alm_pedido_detalle.id_medicamento)
            WHERE far_alm_pedido_detalle.id_pedido=$id_pedido AND far_alm_pedido.estado=3 AND far_alm_pedido.id_bodega=$id_bodega";

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM ($sqlc) AS c";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM ($sqlc) AS c". $where;
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT id_ped_detalle,id_med,cod_medicamento,nom_medicamento,cantidad_ord,cantidad_ing,cantidad_pen FROM ($sqlc) AS c" . $where . " ORDER BY $col $dir $limit";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $data[] = [
            "id_ped_detalle" => $obj['id_ped_detalle'],
            "id_med" => $obj['id_med'],            
            "cod_medicamento" => $obj['cod_medicamento'],
            "nom_medicamento" => $obj['nom_medicamento'],
            "cantidad_ord" => $obj['cantidad_ord'],
            "cantidad_ing" => $obj['cantidad_ing'],
            "cantidad_pen" => $obj['cantidad_pen']
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);

   