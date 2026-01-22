<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../common/funciones_generales.php';

$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$limit = "";
if ($length != -1) {
    $limit = "LIMIT $start, $length";
}
$col = $_POST['order'][0]['column'] + 1;
$dir = $_POST['order'][0]['dir'];
$idusr = $_SESSION['id_user'];
$idrol = $_SESSION['rol'];

$where_usr = " WHERE OI.estado=2";
$where_usr.= " AND OI.id_ingreso NOT IN (SELECT id_ingreso FROM far_traslado WHERE id_ingreso IS NOT NULL AND estado<>0)
               AND OI.id_ingreso NOT IN (SELECT id_ingreso FROM far_traslado_r WHERE id_ingreso IS NOT NULL AND estado<>0)";
               
if($idrol !=1){
    $where_usr .= " AND OI.id_bodega IN (SELECT id_bodega FROM seg_bodegas_usuario WHERE id_usuario=$idusr)";
}

$where = $where_usr;
if (isset($_POST['num_ingreso']) && $_POST['num_ingreso']) {
    $where .= " AND OI.num_ingreso='" . $_POST['num_ingreso'] . "'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND OI.fec_ingreso BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
       
    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_orden_ingreso AS OI" . $where_usr;
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM far_orden_ingreso AS OI" . $where;
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT OI.*,TE.nom_tercero,
                SO.id_sede AS id_sede_origen,SO.nom_sede AS nom_sede_origen,
                BO.id_bodega AS id_bodega_origen,BO.nombre AS nom_bodega_origen
            FROM far_orden_ingreso AS OI
            INNER JOIN tb_terceros AS TE ON (TE.id_tercero = OI.id_provedor)
            INNER JOIN tb_sedes AS SO ON (SO.id_sede = OI.id_sede)
            INNER JOIN far_bodegas AS BO ON (BO.id_bodega = OI.id_bodega)"
            . $where . " ORDER BY $col $dir $limit";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id = $obj['id_ingreso'];
        $imprimir =  '<a value="' . $id . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb btn_imprimir" title="imprimir"><span class="fas fa-print fa-lg"></span></a>';
        $data[] = [
            "id_ingreso" => $id,
            "num_ingreso" => $obj['num_ingreso'],
            "fec_ingreso" => $obj['fec_ingreso'],
            "detalle" => $obj['detalle'],
            "nom_tercero" => $obj['nom_tercero'],
            "id_sede_origen" => $obj['id_sede_origen'],
            "nom_sede_origen" => $obj['nom_sede_origen'],
            "id_bodega_origen" => $obj['id_bodega_origen'],
            "nom_bodega_origen" => $obj['nom_bodega_origen'],
            "botones" => '<div class="text-center centro-vertical">' . $imprimir . '</div>'
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);

   