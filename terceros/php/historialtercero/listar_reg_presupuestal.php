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

    /*
    $sql = "SELECT
            COUNT(*) AS filas
                , pto_crp.id_pto_crp
                , pto_crp.id_manu
                , DATE_FORMAT(pto_crp.fecha,'%Y-%m-%d') AS fecha
                , 'CRP' AS tipo
                , pto_crp.num_contrato
                , SUM(IFNULL(pto_crp_detalle2.valor,0)) AS vr_crp
                , SUM(IFNULL(pto_crp_detalle2.valor_liberado,0)) AS vr_crp_liberado
                , SUM(IFNULL(pto_cop_detalle.valor,0)) AS vr_cop
                , SUM(IFNULL(pto_cop_detalle.valor_liberado,0)) AS vr_cop_liberado
                , SUM(IFNULL(pto_crp_detalle2.valor,0)) - SUM(IFNULL(pto_crp_detalle2.valor_liberado,0)) AS vr_registro
                ,(SUM(IFNULL(pto_crp_detalle2.valor,0)) - SUM(IFNULL(pto_crp_detalle2.valor_liberado,0)))-(SUM(IFNULL(pto_cop_detalle.valor,0)) - SUM(IFNULL(pto_cop_detalle.valor_liberado,0))) AS vr_saldo
                , CASE pto_crp.estado WHEN 1 THEN 'Pendiente' WHEN 2 THEN 'Cerrado' WHEN 0 THEN 'Anulado' END AS estado
            FROM
                pto_cop_detalle
                INNER JOIN (SELECT id_pto_crp,id_pto_crp_det,id_pto_cdp_det,SUM(valor) AS valor,SUM(valor_liberado) AS valor_liberado FROM pto_crp_detalle GROUP BY id_pto_crp) AS pto_crp_detalle2 ON (pto_cop_detalle.id_pto_crp_det = pto_crp_detalle2.id_pto_crp_det)
                INNER JOIN pto_cdp_detalle ON (pto_crp_detalle2.id_pto_cdp_det = pto_cdp_detalle.id_pto_cdp_det)
                INNER JOIN pto_crp ON (pto_crp_detalle2.id_pto_crp = pto_crp.id_pto_crp)
            WHERE pto_crp.id_cdp = $id_cdp
            GROUP BY pto_crp.id_cdp";

            */
    $sql = "SELECT
            COUNT(*) AS filas
                , pto_crp.id_pto_crp
                , pto_crp.id_manu
                , DATE_FORMAT(pto_crp.fecha,'%Y-%m-%d') AS fecha
                , 'CRP' AS tipo
                , pto_crp.num_contrato
                , SUM(IFNULL(pto_crp_detalle2.valor,0)) AS vr_crp
                , SUM(IFNULL(pto_crp_detalle2.valor_liberado,0)) AS vr_crp_liberado
                , SUM(IFNULL(pto_cop_detalle.valor,0)) AS vr_cop
                , SUM(IFNULL(pto_cop_detalle.valor_liberado,0)) AS vr_cop_liberado
                , SUM(IFNULL(pto_crp_detalle2.valor,0)) - SUM(IFNULL(pto_crp_detalle2.valor_liberado,0)) AS vr_registro
                ,(SUM(IFNULL(pto_crp_detalle2.valor,0)) - SUM(IFNULL(pto_crp_detalle2.valor_liberado,0)))-(SUM(IFNULL(pto_cop_detalle.valor,0)) - SUM(IFNULL(pto_cop_detalle.valor_liberado,0))) AS vr_saldo
                , CASE pto_crp.estado WHEN 1 THEN 'Pendiente' WHEN 2 THEN 'Cerrado' WHEN 0 THEN 'Anulado' END AS estado
            FROM
                (SELECT id_pto_crp,id_pto_crp_det,id_pto_cdp_det,SUM(valor) AS valor,SUM(valor_liberado) AS valor_liberado FROM pto_crp_detalle GROUP BY id_pto_crp) AS pto_crp_detalle2
                LEFT JOIN pto_cop_detalle ON (pto_cop_detalle.id_pto_crp_det = pto_crp_detalle2.id_pto_crp_det)
                INNER JOIN pto_cdp_detalle ON (pto_crp_detalle2.id_pto_cdp_det = pto_cdp_detalle.id_pto_cdp_det)
                INNER JOIN pto_crp ON (pto_crp_detalle2.id_pto_crp = pto_crp.id_pto_crp)
            WHERE pto_crp.id_cdp = $id_cdp
            GROUP BY pto_crp.id_cdp";

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
        $id_crp = $obj['id_pto_crp'];
        $saldo = $obj['vr_saldo'];
        $totalRecords=$obj['filas'];
        $totalRecordsFilter=$obj['filas'];

        $liberar = null;
        $liberaciones = null;

        if (PermisosUsuario($permisos, 5401, 3) || $id_rol == 1) {
            if ($saldo > 0 || $saldo < 0) {
                $liberar =  '<a value="' . $id_crp . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb btn_liberar_crp" title="Liberar"><span class="fas fa-arrow-alt-circle-left fa-lg"></span></a>';
                $liberaciones =  '<a value="' . $id_crp . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb btn_liberaciones_crp" title="Listar liberaciones"><span class="fas fa-hand-holding-usd fa-lg"></span></a>';
            }
        }

        $data[] = [
            "id_pto_crp" => $obj['id_pto_crp'], 
            "id_manu" => $obj['id_manu'],             
            "fecha" => $obj['fecha'],             
            "tipo" => $obj['tipo'], 
            "num_contrato" => $obj['num_contrato'], 
            "vr_registro" => $obj['vr_registro'], 
            "vr_saldo" => $obj['vr_saldo'],
            "estado" => $obj['estado'],
            "botones" => '<div class="text-center centro-vertical">' . $liberar . $liberaciones . '</div>',
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
