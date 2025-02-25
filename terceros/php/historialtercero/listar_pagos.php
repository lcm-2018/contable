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
            , pto_cdp_detalle.id_pto_cdp
            , ctb_doc.id_manu
            , DATE_FORMAT(ctb_doc.fecha, '%Y-%m-%d') AS fecha
            , ctb_doc.detalle
            , IFNULL(pto_pag_detalle.valor,0)-IFNULL(pto_pag_detalle.valor_liberado,0) AS valorpagado
        FROM
            pto_pag_detalle
            INNER JOIN ctb_doc ON (pto_pag_detalle.id_ctb_doc = ctb_doc.id_ctb_doc)
            INNER JOIN pto_cop_detalle ON (pto_pag_detalle.id_pto_cop_det = pto_cop_detalle.id_pto_cop_det)
            INNER JOIN pto_crp_detalle ON (pto_cop_detalle.id_pto_crp_det = pto_crp_detalle.id_pto_crp_det)
            INNER JOIN pto_cdp_detalle ON (pto_crp_detalle.id_pto_cdp_det = pto_cdp_detalle.id_pto_cdp_det)
        WHERE pto_cdp_detalle.id_pto_cdp  = $id_cdp";

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
            "detalle" => $obj['detalle'], 
            "valorpagado" => $obj['valorpagado'], 
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
