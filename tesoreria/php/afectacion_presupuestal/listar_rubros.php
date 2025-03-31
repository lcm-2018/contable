<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';

$id_pto_rad = $_POST['id_pto_rad'];

$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$limit = "";
$where = ""; // este seria en caso de haber filtros
if ($length != -1) {
    $limit = "LIMIT $start, $length";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM pto_rad_detalle";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM pto_rad_detalle $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT
                pto_rad_detalle.id_pto_rad_det
                , pto_rad_detalle.id_rubro
                , pto_cargue.cod_pptal
                , pto_cargue.nom_rubro
                , pto_rad_detalle.valor
            FROM
                pto_rad_detalle
                INNER JOIN pto_cargue ON (pto_rad_detalle.id_rubro = pto_cargue.id_cargue)
            WHERE pto_rad_detalle.id_pto_rad=$id_pto_rad";  

    $rs = $cmd->query($sql);
    $obj_rubros = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

//$totalRecords = 0;
//$totalRecordsFilter = 0;

$eliminar = NULL;
$data = [];
if (!empty($obj_rubros)) {
    foreach ($obj_rubros as $obj) {
        $id_pto_rad_det = $obj['id_pto_rad_det'];
        //$totalRecords = $obj['filas'];
        //$totalRecordsFilter = $obj['filas'];

        /*Permisos del usuario
           5201-Opcion [Terceros][Gestion]
            1-Consultar, 2-Adicionar, 3-Modificar, 4-Eliminar, 5-Anular, 6-Imprimir
            5201 gestion de terceros
            5401 presupuesto gestion
        
        if (PermisosUsuario($permisos, 5201, 1) || $id_rol == 1 || PermisosUsuario($permisos, 5401, 1)) {
            $listar = '<a value="' . $id_cdp . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_listar" title="Listar"><span class="fas fa-clipboard-list fa-lg"></span></a>';
        }*/
        $eliminar = '<a value="' . $id_pto_rad_det . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar_rubro" title="Listar"><span class="fas fa-minus fa-lg"></span></a>';
        $data[] = [
            "id_pto_rad_det" => $obj['id_pto_rad_det'],
            "rubro" => $obj['cod_pptal'] . "-" . mb_strtoupper($obj['nom_rubro']),
            "valor" => $obj['valor'],
            "botones" => '<div class="text-center centro-vertical">' . $eliminar . '</div>',
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
