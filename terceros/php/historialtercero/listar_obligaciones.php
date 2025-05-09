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
            ctb_doc.id_manu
            , pto_cop_detalle.id_ctb_doc
            , pto_cdp_detalle.id_pto_cdp   
            , DATE_FORMAT(ctb_doc.fecha, '%Y-%m-%d') AS fecha
            , ctb_factura.num_doc     
            , pto_cop_detalle.valor-IFNULL(pto_cop_detalle.valor_liberado,0) AS valorcausado
            , SUM(IFNULL(ctb_causa_retencion.valor_retencion,0)) AS descuentos
            , SUM(IFNULL(pto_pag_detalle.valor,0)- IFNULL(pto_pag_detalle.valor_liberado,0)) AS neto       
            , CASE ctb_doc.estado WHEN 1 THEN 'Pendiente' WHEN 2 THEN 'Cerrado' WHEN 0 THEN 'Anulado' END AS estado
            , CASE WHEN ((pto_cop_detalle.valor-IFNULL(pto_cop_detalle.valor_liberado,0))-(SUM(IFNULL(ctb_causa_retencion.valor_retencion,0)))-(SUM(IFNULL(pto_pag_detalle.valor,0)- IFNULL(pto_pag_detalle.valor_liberado,0)))) = 0 THEN 'pagado' ELSE 'causado' END AS est
            , COUNT(*) OVER() AS filas
        FROM
            pto_crp_detalle
            INNER JOIN pto_cdp_detalle ON (pto_crp_detalle.id_pto_cdp_det = pto_cdp_detalle.id_pto_cdp_det)
            INNER JOIN pto_cop_detalle ON (pto_cop_detalle.id_pto_crp_det = pto_crp_detalle.id_pto_crp_det)
            INNER JOIN ctb_doc ON (pto_cop_detalle.id_ctb_doc = ctb_doc.id_ctb_doc)
            INNER JOIN ctb_factura ON (ctb_factura.id_ctb_doc = ctb_doc.id_ctb_doc)
            LEFT JOIN ctb_causa_retencion ON (ctb_causa_retencion.id_ctb_doc = ctb_doc.id_ctb_doc)
            LEFT JOIN pto_pag_detalle ON (pto_pag_detalle.id_ctb_doc = ctb_doc.id_ctb_doc)
        WHERE pto_cdp_detalle.id_pto_cdp = $id_cdp
        GROUP BY  ctb_doc.id_ctb_doc";

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
            "id_ctb_doc" => $obj['id_ctb_doc'],             
            "fecha" => $obj['fecha'],             
            "num_doc" => $obj['num_doc'], 
            "valorcausado" => $obj['valorcausado'], 
            "descuentos" => $obj['descuentos'], 
            "neto" => $obj['neto'],
            "estado" => $obj['est'],
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
