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
if ($length != -1){
    $limit = "LIMIT $start, $length";
}
$col = $_POST['order'][0]['column']+1;
$dir = $_POST['order'][0]['dir'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $sql = "SELECT
            count(*) AS filas
            , pto_cdp.id_pto_cdp
            , DATE_FORMAT(pto_crp.fecha, '%Y-%m-%d') AS fecha
            , pto_crp.id_manu
            , 'CRP' AS tipo
            , pto_crp.objeto
            , pto_crp.num_contrato
            , pto_crp_detalle.valor - IFNULL(pto_crp_detalle.valor_liberado,0) AS vr_registro
            , (pto_cdp_detalle.valor - IFNULL(pto_cdp_detalle.valor_liberado,0))-(pto_crp_detalle.valor - IFNULL(pto_crp_detalle.valor_liberado,0)) AS vr_saldo
            , CASE pto_crp.estado WHEN 1 THEN 'Pendiente' WHEN 2 THEN 'Cerrado' WHEN 0 THEN 'Anulado' END AS estado
        FROM
            pto_cdp_detalle
            INNER JOIN pto_cdp ON (pto_cdp_detalle.id_pto_cdp = pto_cdp.id_pto_cdp)
            INNER JOIN pto_crp_detalle ON (pto_crp_detalle.id_pto_cdp_det = pto_cdp_detalle.id_pto_cdp_det)
            INNER JOIN pto_crp ON (pto_crp_detalle.id_pto_crp = pto_crp.id_pto_crp)
        WHERE pto_cdp.id_pto_cdp = $id_cdp";

    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$totalRecords=0;
$totalRecordsFilter=0;

$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $totalRecords=$obj['filas'];
        $totalRecordsFilter=$obj['filas'];

        $data[] = [
            "id_manu" => $obj['id_manu'],             
            "fecha" => $obj['fecha'],             
            "tipo" => $obj['tipo'], 
            "num_contrato" => $obj['num_contrato'], 
            "vr_registro" => $obj['vr_registro'], 
            "vr_saldo" => $obj['vr_saldo'],
            "estado" => $obj['estado'],
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
