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

    $sql = "WITH
                docs_in_cdp AS (
                SELECT DISTINCT pc.id_ctb_doc
                FROM pto_crp_detalle pcr
                JOIN pto_cdp_detalle pcd ON pcr.id_pto_cdp_det = pcd.id_pto_cdp_det
                JOIN pto_cop_detalle pc ON pc.id_pto_crp_det = pcr.id_pto_crp_det
                WHERE pcd.id_pto_cdp = $id_cdp
                ),
                cop_sum AS (
                SELECT id_ctb_doc,
                    SUM(valor - IFNULL(valor_liberado,0)) AS valorcausado
                FROM pto_cop_detalle
                GROUP BY id_ctb_doc
                ),
                ret_sum AS (
                SELECT id_ctb_doc,
                    SUM(IFNULL(valor_retencion,0)) AS descuentos
                FROM ctb_causa_retencion
                GROUP BY id_ctb_doc
                ),
                pag_sum AS (
                SELECT id_ctb_doc,
                    SUM(IFNULL(valor,0) - IFNULL(valor_liberado,0)) AS neto
                FROM pto_pag_detalle
                GROUP BY id_ctb_doc
                )
                SELECT
                    ctb_doc.id_manu,
                    ctb_doc.id_ctb_doc,
                    DATE_FORMAT(ctb_doc.fecha, '%Y-%m-%d') AS fecha,
                    ctb_factura.num_doc,
                    IFNULL(cop_sum.valorcausado, 0) AS valorcausado,
                    IFNULL(ret_sum.descuentos, 0) AS descuentos,
                    IFNULL(pag_sum.neto, 0) AS neto,
                    CASE ctb_doc.estado WHEN 1 THEN 'Pendiente' WHEN 2 THEN 'Cerrado' WHEN 0 THEN 'Anulado' END AS estado,
                    CASE WHEN (IFNULL(cop_sum.valorcausado,0) - IFNULL(ret_sum.descuentos,0) - IFNULL(pag_sum.neto,0)) = 0
                    THEN 'pagado' ELSE 'causado' END AS est,
                    COUNT(*) OVER() AS filas
                FROM docs_in_cdp dic
                JOIN ctb_doc ON ctb_doc.id_ctb_doc = dic.id_ctb_doc
                LEFT JOIN ctb_factura ON ctb_factura.id_ctb_doc = ctb_doc.id_ctb_doc
                LEFT JOIN cop_sum ON cop_sum.id_ctb_doc = ctb_doc.id_ctb_doc
                LEFT JOIN ret_sum ON ret_sum.id_ctb_doc = ctb_doc.id_ctb_doc
                LEFT JOIN pag_sum ON pag_sum.id_ctb_doc = ctb_doc.id_ctb_doc
                ORDER BY ctb_doc.id_ctb_doc";

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
