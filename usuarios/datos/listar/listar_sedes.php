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
$col = $_POST['order'][0]['column'] ? $_POST['order'][0]['column'] : 1;
$dir = $_POST['order'][0]['dir'];
$idusr = $_SESSION['id_user'];
$idrol = $_SESSION['rol'];

$checked = isset($_POST['selfil']) && $_POST['selfil'] == 1 ? "checked" : "";

$where_usr = " WHERE 1";
if($idrol !=1){
    $where_usr .= " AND tb_sedes.id_sede IN (SELECT id_sede FROM seg_sedes_usuario WHERE id_usuario=$idusr)";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta de sedes
    $sql = "SELECT COUNT(*) AS total FROM tb_sedes $where_usr";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT tb_sedes.id_sede, tb_sedes.nom_sede, tb_sedes.dir_sede, tb_sedes.tel_sede
            FROM tb_sedes";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id = $obj['id_sede'];
        $data[] = [
            "select" => '<input type="checkbox" name="sed[]" value="' . $id . '" ' . $checked . '>',
            "id_sede" => $id,
            "nom_sede" => mb_strtoupper($obj['nom_sede']),            
            "dir_sede" => mb_strtoupper($obj['dir_sede']),
            "tel_sede" => $obj['tel_sede'],
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);