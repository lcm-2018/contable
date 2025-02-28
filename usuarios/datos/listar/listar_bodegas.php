<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
include '../../common/funciones_generales.php';

$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$limit = "";
if ($length != -1) {
    $limit = "LIMIT $start, $length";
}
//$col = $_POST['order'][0]['column'] ? $_POST['order'][0]['column'] : 1;
//$dir = $_POST['order'][0]['dir'];
$idusr = $_SESSION['id_user'];
$idrol = $_SESSION['rol'];

$checked = isset($_POST['selfil']) && $_POST['selfil'] == 1 ? "checked" : "";

$where_usr = " WHERE 1";
if ($idrol != 1) {
    $where_usr .= " AND far_bodegas.id_bodega IN (SELECT id_bodega FROM seg_bodegas_usuario WHERE id_usuario=$idusr)";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta de bodegas
    $sql = "SELECT COUNT(*) AS total FROM far_bodegas $where_usr";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];
    $totalRecordsFilter = $total['total'];

    $idsed = isset($_POST['sed']) ? implode(",", $_POST['sed']) : '';
    if ($idsed != '') {
        //Consulta los datos para listarlos en la tabla
        $sql = "SELECT far_bodegas.id_bodega, far_bodegas.nombre, 
        CASE far_bodegas.tipo WHEN 1 THEN 'PRINCIPAL' END AS tipo, 
        Case far_bodegas.estado WHEN 0 THEN 'INACTIVO' WHEN 1 THEN 'ACTIVO' END as estado 
        FROM far_bodegas
        INNER JOIN tb_sedes_bodega ON (tb_sedes_bodega.id_bodega=far_bodegas.id_bodega)
        WHERE tb_sedes_bodega.id_sede IN ($idsed)";
        $rs = $cmd->query($sql);
        $objs = $rs->fetchAll();
        $cmd = null;
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id = $obj['id_bodega'];
        $data[] = [
            "select" => '<input type="checkbox" name="bod[]" value="' . $id . '" ' . $checked . ' class="chk_bodegas">',
            "id_bodega" => $id,
            "nombre" => mb_strtoupper($obj['nombre']),
            "tipo" => $obj['tipo'],
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