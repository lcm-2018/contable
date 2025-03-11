<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';

$id_cdp = $_POST['id_cdp'];

$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$limit = "";
if ($length != -1) {
    $limit = "LIMIT $start, $length";
}
$col = $_POST['order'][0]['column'] + 1;
$dir = $_POST['order'][0]['dir'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $sql = "SELECT
                COUNT(*) AS filas
                ,pto_cdp_detalle.id_pto_cdp
                ,pto_cdp_detalle.id_rubro
                , pto_cargue.cod_pptal
                ,((SUM(pto_cdp_detalle.valor) - SUM(pto_cdp_detalle.valor_liberado)) - (SUM(pto_crp_detalle.valor) - SUM(IFNULL(pto_crp_detalle.valor_liberado, 0)))) AS saldo_final
            FROM
                pto_crp_detalle
                INNER JOIN pto_cdp_detalle ON (pto_crp_detalle.id_pto_cdp_det = pto_cdp_detalle.id_pto_cdp_det)
                INNER JOIN pto_cargue ON (pto_cdp_detalle.id_rubro = pto_cargue.id_cargue)
            WHERE pto_cdp_detalle.id_pto_cdp = $id_cdp
            GROUP BY pto_cdp_detalle.id_pto_cdp,pto_cdp_detalle.id_rubro";

    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$totalRecords = 0;
$totalRecordsFilter = 0;

$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $totalRecords = $obj['filas'];
        $totalRecordsFilter = $obj['filas'];

        $data[] = [
            "id_rubro" => $obj['id_rubro'],
            "cod_pptal" => $obj['cod_pptal'],
            "saldo_final" => $obj['saldo_final'],
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
