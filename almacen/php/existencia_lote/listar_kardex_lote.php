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

$where_art = " WHERE far_kardex.id_lote=" . $_POST['id_lote'] . " AND (far_kardex.can_ingreso>0 OR far_kardex.can_egreso>0) AND far_kardex.estado=1";
$where = "";
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where = " AND far_kardex.fec_movimiento BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_kardex $where_art";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM far_kardex $where_art $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT id_kardex,fec_movimiento,comprobante,nom_sede,nom_bodega,lote,detalle,val_ingreso,val_promedio,can_ingreso,can_egreso,existencia_lote
            FROM (
                SELECT far_kardex.id_kardex,far_kardex.fec_movimiento,CONCAT_WS('-','I',far_orden_ingreso.num_ingreso) AS comprobante,
                    tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,far_medicamento_lote.lote,far_kardex.detalle,
                    far_kardex.val_ingreso,far_kardex.val_promedio,far_kardex.can_ingreso,far_kardex.can_egreso,far_kardex.existencia_lote
                FROM far_kardex
                INNER JOIN far_orden_ingreso ON (far_kardex.id_ingreso = far_orden_ingreso.id_ingreso)
                INNER JOIN tb_sedes ON (far_kardex.id_sede = tb_sedes.id_sede)
                INNER JOIN far_bodegas ON (far_kardex.id_bodega = far_bodegas.id_bodega)
                INNER JOIN far_medicamento_lote ON (far_kardex.id_lote= far_medicamento_lote.id_lote) $where_art $where                
                UNION ALL
                SELECT far_kardex.id_kardex,far_kardex.fec_movimiento,CONCAT_WS('-','E',far_orden_egreso.num_egreso) AS comprobante,
			        tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,far_medicamento_lote.lote,far_kardex.detalle,
			        far_kardex.val_ingreso,far_kardex.val_promedio,far_kardex.can_ingreso,far_kardex.can_egreso,far_kardex.existencia_lote
                FROM far_kardex
                INNER JOIN far_orden_egreso ON (far_kardex.id_egreso = far_orden_egreso.id_egreso)
                INNER JOIN tb_sedes ON (far_kardex.id_sede = tb_sedes.id_sede)
                INNER JOIN far_bodegas ON (far_kardex.id_bodega = far_bodegas.id_bodega)
                INNER JOIN far_medicamento_lote ON (far_kardex.id_lote= far_medicamento_lote.id_lote) $where_art $where 
                UNION ALL
                SELECT far_kardex.id_kardex,far_kardex.fec_movimiento,CONCAT_WS('-','TE',far_traslado.num_traslado) AS comprobante,
			        tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,far_medicamento_lote.lote,far_kardex.detalle,
			        far_kardex.val_ingreso,far_kardex.val_promedio,far_kardex.can_ingreso,far_kardex.can_egreso,far_kardex.existencia_lote
                FROM far_kardex
                INNER JOIN far_traslado ON (far_kardex.id_egreso_tra = far_traslado.id_traslado)
                INNER JOIN tb_sedes ON (far_kardex.id_sede = tb_sedes.id_sede)
                INNER JOIN far_bodegas ON (far_kardex.id_bodega = far_bodegas.id_bodega)
                INNER JOIN far_medicamento_lote ON (far_kardex.id_lote= far_medicamento_lote.id_lote) $where_art $where 
                UNION ALL
                SELECT far_kardex.id_kardex,far_kardex.fec_movimiento,CONCAT_WS('-','TI',far_traslado.num_traslado) AS comprobante,
			        tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,far_medicamento_lote.lote,far_kardex.detalle,
			        far_kardex.val_ingreso,far_kardex.val_promedio,far_kardex.can_ingreso,far_kardex.can_egreso,far_kardex.existencia_lote
                FROM far_kardex
                INNER JOIN far_traslado ON (far_kardex.id_ingreso_tra = far_traslado.id_traslado)
                INNER JOIN tb_sedes ON (far_kardex.id_sede = tb_sedes.id_sede)
                INNER JOIN far_bodegas ON (far_kardex.id_bodega = far_bodegas.id_bodega)
                INNER JOIN far_medicamento_lote ON (far_kardex.id_lote= far_medicamento_lote.id_lote) $where_art $where
            ) AS t ORDER BY $col $dir $limit";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id = $obj['id_kardex'];
        $data[] = [
            "id_kardex" => $id,            
            "fec_movimiento" => $obj['fec_movimiento'],
            "comprobante" => $obj['comprobante'],
            "nom_sede" => mb_strtoupper($obj['nom_sede']),
            "nom_bodega" => mb_strtoupper($obj['nom_bodega']),
            "lote" => $obj['lote'],
            "detalle" => $obj['detalle'],
            "val_ingreso" => formato_valor($obj['val_ingreso']),
            "val_promedio" => formato_valor($obj['val_promedio']),
            "can_ingreso" => $obj['can_ingreso'],
            "can_egreso" => $obj['can_egreso'],
            "existencia_lote" => $obj['existencia_lote']
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
