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
$idusr = $_SESSION['id_user'];
$idrol = $_SESSION['rol'];

$where_usr = " WHERE far_orden_egreso.id_tipo_egreso NOT IN (1,2) AND far_orden_egreso.id_ingreso IS NULL";
if($idrol !=1){
    $where_usr .= " AND far_orden_egreso.id_bodega IN (SELECT id_bodega FROM seg_bodegas_usuario WHERE id_usuario=$idusr)";
}

$where = "";
if (isset($_POST['id_sede']) && $_POST['id_sede']) {
    $where .= " AND far_orden_egreso.id_sede='" . $_POST['id_sede'] . "'";
}
if (isset($_POST['id_bodega']) && $_POST['id_bodega']) {
    $where .= " AND far_orden_egreso.id_bodega='" . $_POST['id_bodega'] . "'";
}
if (isset($_POST['id_egr']) && $_POST['id_egr']) {
    $where .= " AND far_orden_egreso.id_egreso='" . $_POST['id_egr'] . "'";
}
if (isset($_POST['num_egr']) && $_POST['num_egr']) {
    $where .= " AND far_orden_egreso.num_egreso='" . $_POST['num_egr'] . "'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND far_orden_egreso.fec_egreso BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['id_tercero']) && $_POST['id_tercero']) {
    $where .= " AND far_orden_egreso.id_cliente=" . $_POST['id_tercero'] . "";
}
if (isset($_POST['id_cencost']) && $_POST['id_cencost']) {
    $where .= " AND far_orden_egreso.id_centrocosto=" . $_POST['id_cencost'] . "";
}
if (isset($_POST['id_tipegr']) && $_POST['id_tipegr']) {
    $where .= " AND far_orden_egreso.id_tipo_egreso=" . $_POST['id_tipegr'] . "";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND far_orden_egreso.estado=" . $_POST['estado'];
}
if (isset($_POST['estado']) && strlen($_POST['modulo'])) {
    $where .= " AND far_orden_egreso.creado_far=" . $_POST['modulo'];
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_orden_egreso $where_usr";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM far_orden_egreso $where_usr $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT far_orden_egreso.id_egreso,far_orden_egreso.num_egreso,far_orden_egreso.fec_egreso,far_orden_egreso.hor_egreso,
	            far_orden_egreso.detalle,tb_terceros.nom_tercero,tb_centrocostos.nom_centro,
	            far_orden_egreso_tipo.nom_tipo_egreso,far_orden_egreso.val_total,tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,
                far_orden_egreso.estado,
	            CASE far_orden_egreso.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' WHEN 0 THEN 'ANULADO' END AS nom_estado
            FROM far_orden_egreso
            INNER JOIN far_orden_egreso_tipo ON (far_orden_egreso_tipo.id_tipo_egreso=far_orden_egreso.id_tipo_egreso)
            INNER JOIN tb_terceros ON (tb_terceros.id_tercero=far_orden_egreso.id_cliente)
            INNER JOIN tb_centrocostos ON (tb_centrocostos.id_centro=far_orden_egreso.id_centrocosto)
            INNER JOIN tb_sedes ON (tb_sedes.id_sede=far_orden_egreso.id_sede)
            INNER JOIN far_bodegas ON (far_bodegas.id_bodega=far_orden_egreso.id_bodega)
            $where_usr $where ORDER BY $col $dir $limit";

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
        $id = $obj['id_egreso'];
        //Permite crear botones en la cuadricula si tiene permisos de 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
        if (PermisosUsuario($permisos, 5007, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5007, 4) || $id_rol == 1) {
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id_egreso" => $id,
            "num_egreso" => $obj['num_egreso'],
            "fec_egreso" => $obj['fec_egreso'],
            "hor_egreso" => $obj['hor_egreso'],
            "detalle" => $obj['detalle'],
            "nom_tipo_egreso" => mb_strtoupper($obj['nom_tipo_egreso']),
            "nom_tercero" => mb_strtoupper($obj['nom_tercero']),
            "nom_centro" => mb_strtoupper($obj['nom_centro']),                        
            "nom_sede" => mb_strtoupper($obj['nom_sede']),
            "nom_bodega" => mb_strtoupper($obj['nom_bodega']),
            "val_total" => formato_valor($obj['val_total']),
            "estado" => $obj['estado'],
            "nom_estado" => $obj['nom_estado'],            
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
