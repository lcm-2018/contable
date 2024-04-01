<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
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
$where_gen = " WHERE far_medicamento_lote.id_bodega=$id_bodega AND far_medicamento_lote.estado=1 AND far_medicamentos.estado=1";

$where = $where_gen;
if (isset($_POST['codigo']) && $_POST['codigo']) {
    $where .= " AND far_medicamentos.cod_medicamento LIKE '" . $_POST['codigo'] . "%'";
}
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND far_medicamentos.nom_medicamento LIKE '" . $_POST['nombre'] . "%'";
}
if (isset($_POST['no_vencidos']) && $_POST['no_vencidos']) {
    $where .= " AND far_medicamento_lote.fec_vencimiento>='" . date('Y-m-d') . "'";
}
if (isset($_POST['con_existencia']) && $_POST['con_existencia']) {
    $where .= " AND far_medicamento_lote.existencia>0";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_medicamento_lote
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)" . $where_gen;
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM far_medicamento_lote
    INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)" . $where;
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT far_medicamento_lote.id_lote,far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,
	            far_medicamento_lote.lote,far_presentacion_lote.nom_presentacion,
                ROUND(far_medicamento_lote.existencia/far_presentacion_lote.cantidad,1) AS existencia_umpl,
	            far_medicamento_lote.existencia,far_medicamentos.val_promedio,far_medicamento_lote.fec_vencimiento
            FROM far_medicamento_lote
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
            INNER JOIN far_presentacion_lote ON (far_presentacion_lote.id_presentacion=far_medicamento_lote.id_presentacion)"
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
        $data[] = [
            "id_lote" => $obj['id_lote'],
            "cod_medicamento" => $obj['cod_medicamento'],
            "nom_medicamento" => $obj['nom_medicamento'],
            "lote" => $obj['lote'],
            "nom_presentacion" => $obj['nom_presentacion'],
            "existencia_umpl" => $obj['existencia_umpl'],            
            "existencia" => $obj['existencia'],
            "val_promedio" => formato_valor($obj['val_promedio']),
            "fec_vencimiento" => $obj['fec_vencimiento']
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);

   