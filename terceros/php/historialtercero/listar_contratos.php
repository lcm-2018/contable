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
            ctt_novedad_liquidacion.id_liquidacion
            , ctt_contratos.num_contrato
            , DATE_FORMAT(ctt_contratos.fec_ini, '%Y-%m-%d') AS fec_ini
            , DATE_FORMAT(ctt_contratos.fec_fin, '%Y-%m-%d') AS fec_fin
            , ctt_contratos.val_contrato
            , ctt_novedad_adicion_prorroga.val_adicion
            , ctt_novedad_liquidacion.val_cte
            , CASE ctt_novedad_liquidacion.estado WHEN 1 THEN 'Liquidado' ELSE 'En ejecucion' END AS estado
            , COUNT(*) OVER() AS filas
        FROM
            ctt_contratos
            INNER JOIN ctt_adquisiciones ON (ctt_contratos.id_compra = ctt_adquisiciones.id_adquisicion)
            INNER JOIN pto_cdp ON (ctt_adquisiciones.id_cdp = pto_cdp.id_pto_cdp)
            LEFT JOIN ctt_novedad_adicion_prorroga ON (ctt_novedad_adicion_prorroga.id_adq = ctt_contratos.id_contrato_compra)
            LEFT JOIN ctt_novedad_liquidacion ON (ctt_novedad_liquidacion.id_adq = ctt_contratos.id_contrato_compra)
        WHERE ctt_adquisiciones.id_cdp = $id_cdp AND ctt_novedad_liquidacion.id_tipo_nov = 8
        ORDER BY ctt_novedad_liquidacion.id_liquidacion DESC LIMIT 1";

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
            "num_contrato" => $obj['num_contrato'],             
            "fec_ini" => $obj['fec_ini'],             
            "fec_fin" => $obj['fec_fin'], 
            "val_contrato" => $obj['val_contrato'], 
            "val_adicion" => $obj['val_adicion'], 
            "val_cte" => $obj['val_cte'],
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
