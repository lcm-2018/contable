<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
include '../common/funciones_generales.php';

$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$limit = "";
if ($length != -1) {
    $limit = "LIMIT $start, $length";
}
$col = $_POST['order'][0]['column'] + 1;
$dir = $_POST['order'][0]['dir'];

$where = " WHERE far_alm_pedido.tipo=1";

if (isset($_POST['id_pedido']) && $_POST['id_pedido']) {
    $where .= " AND far_alm_pedido.id_pedido='" . $_POST['id_pedido'] . "'";
}
if (isset($_POST['num_pedido']) && $_POST['num_pedido']) {
    $where .= " AND far_alm_pedido.num_pedido='" . $_POST['num_pedido'] . "'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND far_alm_pedido.fec_pedido BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND far_alm_pedido.estado=" . $_POST['estado'];
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_alm_pedido";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM far_alm_pedido $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT far_alm_pedido.id_pedido,far_alm_pedido.num_pedido,far_alm_pedido.fec_pedido,far_alm_pedido.hor_pedido,
	            far_alm_pedido.detalle,far_alm_pedido.val_total,
                tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,far_alm_pedido.estado,
	            CASE far_alm_pedido.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CONFIRMADO' 
                                            WHEN 3 THEN 'ACEPTADO' WHEN 4 THEN 'FINALIZADO'
                                            WHEN 0 THEN 'ANULADO' END AS nom_estado,
                PEDIDO.ingresos
            FROM far_alm_pedido
            INNER JOIN tb_sedes ON (tb_sedes.id_sede=far_alm_pedido.id_sede)
            INNER JOIN far_bodegas ON (far_bodegas.id_bodega=far_alm_pedido.id_bodega)
            LEFT JOIN (SELECT id_pedido,GROUP_CONCAT(id_ingreso) AS ingresos 
                        FROM far_orden_ingreso
                        WHERE id_pedido IS NOT NULL
                        GROUP BY id_pedido
                        ) AS PEDIDO ON (PEDIDO.id_pedido=far_alm_pedido.id_pedido)
            $where ORDER BY $col $dir $limit";

    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$editar = NULL;
$eliminar = NULL;
$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id = $obj['id_pedido'];
        //Permite crear botones en la cuadricula si tiene permisos de 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
        if (PermisosUsuario($permisos, 5005, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5005, 4) || $id_rol == 1) {
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id_pedido" => $id,
            "num_pedido" => $obj['num_pedido'],
            "fec_pedido" => $obj['fec_pedido'],
            "hor_pedido" => $obj['hor_pedido'],
            "detalle" => $obj['detalle'],            
            "nom_sede" => mb_strtoupper($obj['nom_sede']),
            "nom_bodega" => mb_strtoupper($obj['nom_bodega']),
            "val_total" => formato_valor($obj['val_total']),
            "estado" => $obj['estado'],
            "nom_estado" => $obj['nom_estado'],
            "ingresos" => $obj['ingresos'],
            "botones" => '<div class="text-center centro-vertical">' . $editar . $eliminar . '</div>',
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
