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
    $sql = "SELECT COUNT(*) AS total FROM ctb_doc WHERE id_ctb_doc<>0";
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
                c.id_ctb_doc AS documento_credito,
                c.id_manu,
                d.id_ctb_doc_debito AS documento_debito,
                c.id_tercero_api,
                c.nit_tercero,
                c.nom_tercero,
                c.fecha AS fecha_credito,
                d.fecha AS fecha_debito,
                c.sumacredito,
                COALESCE(d.sumadebito, 0) AS sumadebito,
                (c.sumacredito - COALESCE(d.sumadebito, 0)) AS saldo,
                DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) AS antiguedad,
                COALESCE(
                    CASE 
                        WHEN DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) <= 30 
                        THEN (c.sumacredito - COALESCE(d.sumadebito, 0))
                    END, 0) AS menos30,
                COALESCE(
                    CASE 
                        WHEN (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) > 30) 
                        AND (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) <= 60) 
                        THEN (c.sumacredito - COALESCE(d.sumadebito, 0))
                    END, 0) AS de30a60,
                COALESCE(
                CASE 
                    WHEN (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) > 60) 
                    AND (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) <= 90) 
                    THEN (c.sumacredito - COALESCE(d.sumadebito, 0))
                END, 0) AS de60a90,
                COALESCE(
                CASE 
                    WHEN (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) > 90) 
                    AND (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) <= 180) 
                    THEN (c.sumacredito - COALESCE(d.sumadebito, 0))
                    END, 0) AS de90a180,
                COALESCE(
                    CASE 
                    WHEN (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) > 180) 
                    AND (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) <= 360) 
                    THEN (c.sumacredito - COALESCE(d.sumadebito, 0))
                    END, 0) AS de180a360,
                COALESCE(
                CASE 
                    WHEN (DATEDIFF(DATE_FORMAT('$fecha', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) > 360) 
                    THEN (c.sumacredito - COALESCE(d.sumadebito, 0))
                    END, 0) AS mas360,
                COUNT(*) OVER() AS total
            FROM 
                (-- Consulta de Crédito (tipo_doc = 3)
                SELECT
                    ctb_libaux.id_ctb_doc,
                    ctb_doc.id_manu,
                    tb_terceros.id_tercero_api,
                    tb_terceros.nit_tercero,
                    tb_terceros.nom_tercero,
                    DATE_FORMAT(ctb_doc.fecha, '%Y-%m-%d') AS fecha,
                    SUM(ctb_libaux.credito) AS sumacredito
                FROM
                    ctb_libaux
                    INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                    INNER JOIN tb_terceros ON (ctb_libaux.id_tercero_api = tb_terceros.id_tercero_api)
                WHERE ctb_doc.id_tipo_doc = 3
                    AND DATE_FORMAT(ctb_doc.fecha, '%Y-%m-%d') <= '$fecha'
                    AND ctb_libaux.ref = 1
                    AND ctb_doc.estado = 2
                GROUP BY 
                    ctb_libaux.id_ctb_doc, tb_terceros.id_tercero_api
                ) c
            LEFT JOIN 
                (-- Consulta de Débito (tipo_doc = 4)
                SELECT 
                    ctb_doc.id_ctb_doc_tipo3 AS id_ctb_doc_credito,
                    ctb_libaux.id_ctb_doc AS id_ctb_doc_debito,
                    tb_terceros.id_tercero_api,
                    DATE_FORMAT(ctb_doc.fecha, '%Y-%m-%d') AS fecha,
                    SUM(ctb_libaux.debito) AS sumadebito
                FROM 
                    ctb_libaux
                    LEFT JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                    INNER JOIN tb_terceros ON (ctb_libaux.id_tercero_api = tb_terceros.id_tercero_api)
                WHERE ctb_doc.id_tipo_doc = 4
                    AND ctb_doc.id_ctb_doc_tipo3 IS NOT NULL
                    AND DATE_FORMAT(ctb_doc.fecha, '%Y-%m-%d') <= '$fecha'
                GROUP BY 
                    ctb_doc.id_ctb_doc_tipo3
                ) d ON c.id_ctb_doc = d.id_ctb_doc_credito AND c.id_tercero_api = d.id_tercero_api
            WHERE c.sumacredito - COALESCE(d.sumadebito, 0) > 0
            AND c.id_tercero_api <> 3612 -- la Dian
            AND c.id_tercero_api <> 3619 -- estampillas
            ORDER BY 6 asc $limit";

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
            //"id_tercero_api" => $obj['id_tercero_api'],
            "id_manu" => $obj['id_manu'],
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
