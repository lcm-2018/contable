<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';

$fecha = $_POST['fecha'];

$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$limit = "";
if ($length != -1) {
    $limit = "LIMIT $start, $length";
}
$col = $_POST['order'][0]['column'] + 1;
$dir = $_POST['order'][0]['dir'];

//estos where modificarlos con el filtro para buscar por disponibilidad y rango de fechas
$and_where = "";
/*if (isset($_POST['nrodisponibilidad']) && $_POST['nrodisponibilidad']) {
    $where .= " AND far_medicamentos.cod_medicamento LIKE '" . $_POST['codigo'] . "%'";
}
if (isset($_POST['fecini']) && $_POST['fecini'] && isset($_POST['fecfin']) && $_POST['fecfin']) {
    $and_where .= " AND pto_cdp.fecha BETWEEN '" . $_POST['fecini'] . "' AND '" . $_POST['fecfin'] . "'";
}
/*if (isset($_POST['subgrupo']) && $_POST['subgrupo']) {
    $where .= " AND far_medicamentos.id_subgrupo=" . $_POST['subgrupo'];
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND far_medicamentos.estado=" . $_POST['estado'];
}*/
//----------------------------------------------

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM ctb_libaux WHERE id_ctb_libaux<>0";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];
    $totalRecordsFilter = 0;

    //Consulta el total de registros aplicando el filtro
    /*$sql = "(SELECT COUNT(*) AS total FROM (
                SELECT 1
                FROM ctb_libaux
                LEFT JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                INNER JOIN tb_terceros ON (ctb_libaux.id_tercero_api = tb_terceros.id_tercero_api)
                WHERE ctb_doc.id_tipo_doc = 3
                AND DATE_FORMAT(ctb_libaux.fecha_reg, '%Y-%m-%d') <= '$fecha'
                GROUP BY ctb_libaux.id_ctb_doc, tb_terceros.id_tercero_api
            ) AS subquery) ";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];*/
    
    //------Consulta los datos para listarlos en la tabla
    $sql = "SELECT 
                d.ctb_doc_debito,
                d.id_tercero_api,
                tb_terceros.nit_tercero,
                tb_terceros.nom_tercero,
                d.id_tipo_doc AS tipo_doc_debito,
                c.id_tipo_doc AS tipo_doc_credito,
                c.fecha_credito,
                d.fecha_debito,
                d.sumadebito,
                c.sumacredito,
                d.ctb_doc_credito,
                (c.sumacredito - d.sumadebito) AS saldo,
                DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha_credito, '%Y-%m-%d'))) AS antiguedad,
                CASE 
                WHEN DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha_credito, '%Y-%m-%d'))) <= 30 THEN (c.sumacredito - d.sumadebito)
                END AS menos30,
                CASE 
                WHEN (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha_credito, '%Y-%m-%d'))) > 30) && (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha_credito, '%Y-%m-%d'))) <= 60) THEN (c.sumacredito - d.sumadebito)
                END AS de30a60,
                CASE 
                WHEN (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha_credito, '%Y-%m-%d'))) > 60) && (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha_credito, '%Y-%m-%d'))) <= 90) THEN (c.sumacredito - d.sumadebito)
                END AS de60a90,
                CASE 
                WHEN (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha_credito, '%Y-%m-%d'))) > 90) && (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha_credito, '%Y-%m-%d'))) <= 180) THEN (c.sumacredito - d.sumadebito)
                END AS de90a180,
                CASE 
                WHEN (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha_credito, '%Y-%m-%d'))) > 180) && (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha_credito, '%Y-%m-%d'))) <= 360) THEN (c.sumacredito - d.sumadebito)
                END AS de180a360,
                CASE 
                WHEN (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha_credito, '%Y-%m-%d'))) > 360) THEN (c.sumacredito - d.sumadebito)
                END AS mas360,
                COUNT(*) OVER() AS total
            FROM
                (SELECT
                    ctb_libaux.id_ctb_doc AS ctb_doc_debito,
                    tb_terceros.id_tercero_api,
                    ctb_doc.id_tipo_doc,
                    DATE_FORMAT(ctb_libaux.fecha_reg, '%Y-%m-%d') AS fecha_debito,
                    SUM(ctb_libaux.debito) AS sumadebito,
                    pto_cop_detalle.id_ctb_doc AS ctb_doc_credito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                    INNER JOIN tb_terceros ON (ctb_libaux.id_tercero_api = tb_terceros.id_tercero_api)
                    LEFT JOIN pto_pag_detalle ON (ctb_libaux.id_ctb_doc = pto_pag_detalle.id_ctb_doc)
                    LEFT JOIN pto_cop_detalle ON (pto_pag_detalle.id_pto_cop_det = pto_cop_detalle.id_pto_cop_det)
                WHERE ctb_doc.id_tipo_doc = 4
                    AND DATE_FORMAT(ctb_libaux.fecha_reg, '%Y-%m-%d') <= '$fecha'
                GROUP BY pto_cop_detalle.id_ctb_doc) d
            LEFT JOIN
                (SELECT
                    ctb_libaux.id_ctb_doc,
                    tb_terceros.id_tercero_api,
                    ctb_doc.id_tipo_doc,
                    DATE_FORMAT(ctb_libaux.fecha_reg, '%Y-%m-%d') AS fecha_credito,
                    SUM(ctb_libaux.credito) AS sumacredito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                    INNER JOIN tb_terceros ON (ctb_libaux.id_tercero_api = tb_terceros.id_tercero_api)
                WHERE ctb_doc.id_tipo_doc = 3
                    AND DATE_FORMAT(ctb_libaux.fecha_reg, '%Y-%m-%d') <= '$fecha'
                GROUP BY ctb_libaux.id_ctb_doc, tb_terceros.id_tercero_api) c
            ON d.ctb_doc_credito = c.id_ctb_doc AND d.id_tercero_api = c.id_tercero_api
            LEFT JOIN tb_terceros ON (tb_terceros.id_tercero_api = c.id_tercero_api)
            WHERE tb_terceros.nom_tercero IS NOT NULL
            AND (c.sumacredito - d.sumadebito) > 0
            ORDER BY 4 asc $limit";

    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();

    

    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$data = [];
if (!empty($objs)) {
    $totalRecordsFilter = $objs[0]['total'];
    foreach ($objs as $obj) {
        $data[] = [
            "id_tercero_api" => $obj['id_tercero_api'],
            "nit_tercero" => $obj['nit_tercero'],
            "nom_tercero" => mb_strtoupper($obj['nom_tercero']),
            //"id_ctb_doc" => $obj['id_ctb_doc'],
            "fecha_credito" => $obj['fecha_credito'],
            "sumacredito" => $obj['sumacredito'],
            "menos30" => $obj['menos30'],
            "de30a60" => $obj['de30a60'],
            "de60a90" => $obj['de60a90'],
            "de90a180" => $obj['de90a180'],
            "de180a360" => $obj['de180a360'],
            "mas360" => $obj['mas360'],
            "saldo" => $obj['saldo'],
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
