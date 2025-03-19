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
                id_pto_cdp_det
                , DATE_FORMAT(fecha_libera,'%Y-%m-%d') AS fecha
                , concepto_libera
                , valor_liberado    
            FROM pto_cdp_detalle
            WHERE id_pto_cdp = $id_cdp
            AND valor_liberado > 0 ";

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
        $totalRecords = 0;
        $totalRecordsFilter = 0;

        $anular = null;

        if (PermisosUsuario($permisos, 5401, 3) || $id_rol == 1) {
            
        }
        //-----------------------------
        $liberar = null;
        $liberaciones = null;
        if (PermisosUsuario($permisos, 5201, 1) || $id_rol == 1 || PermisosUsuario($permisos, 5401, 1)) {
            $listar = '<a value="' . $id_cdp . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_listar" title="Listar"><span class="fas fa-clipboard-list fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5401, 3) || $id_rol == 1) {
            if ($saldo > 0 || $saldo < 0) {
                $liberar =  '<a value="' . $id_cdp . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb btn_liberar" title="Liberar"><span class="fas fa-arrow-alt-circle-left fa-lg"></span></a>';
                $liberaciones =  '<a value="' . $id_cdp . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb btn_liberaciones" title="Listar liberaciones"><span class="fas fa-hand-holding-usd fa-lg"></span></a>';
            }
        }
        $data[] = [
            "id_tercero_api" => $obj['id_tercero_api'],
            "nit_tercero" => $obj['nit_tercero'],
            "nom_tercero" => mb_strtoupper($obj['nom_tercero']),
            "id_manu" => $obj['id_manu'],
            "id_pto_cdp" => $id_cdp,
            "fecha" => $obj['fecha'],
            "objeto" => mb_strtoupper($obj['objeto']),
            "valor_cdp" => $obj['valor_cdp'],
            "valor_cdp_liberado" => $obj['valor_cdp_liberado'],
            "valor_crp" => $obj['valor_crp'],
            "valor_crp_liberado" => $obj['valor_crp_liberado'],
            "saldo" => $obj['saldo'],
            "botones" => '<div class="text-center centro-vertical">' . $liberar . $listar . $liberaciones . '</div>',
        ];

        //--------------------------------

        $data[] = [
            "id_pto_cdp_det" => $obj['id_pto_cdp_det'],
            "fecha" => $obj['fecha'],
            "concepto_libera" => mb_strtoupper($obj['concepto_libera']),
            "valor_liberado" => $obj['valor_liberado'],
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
