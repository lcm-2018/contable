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

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(DISTINCT ctb_libaux.id_tercero_api) AS total
            FROM
                ctb_libaux 
            WHERE DATE_FORMAT(ctb_libaux.fecha_reg,'%Y-%m-%d') <= '$fecha'";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];
    

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT DISTINCT
                ctb_libaux.id_tercero_api
                , tb_terceros.nit_tercero
                , tb_terceros.nom_tercero
            FROM
                tb_terceros
                INNER JOIN ctb_libaux ON (tb_terceros.id_tercero_api = ctb_libaux.id_tercero_api)
            WHERE DATE_FORMAT(ctb_libaux.fecha_reg,'%Y-%m-%d') <= '$fecha'
            ORDER BY $col $dir $limit";

    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $data[] = [
            "id_tercero_api" => $obj['id_tercero_api'],
            "nit_tercero" => $obj['nit_tercero'],
            "nom_tercero" => mb_strtoupper($obj['nom_tercero']),
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
