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

$where = "";
if (isset($_POST['search']['value']) && $_POST['search']['value']){
    $where .= " AND far_medicamento_lote.lote LIKE '%" . $_POST['search']['value'] . "%'";
}
if (isset($_POST['con_existencia']) && $_POST['con_existencia']){
    $where .= " AND far_medicamento_lote.existencia>0";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $bodega = bodega_principal_general($cmd);
    $bodega_pri = $bodega['id_bodega'];

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_medicamento_lote WHERE id_med=" . $_POST['id_articulo'];
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM far_medicamento_lote WHERE id_med=" . $_POST['id_articulo'] . $where; 
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT far_medicamento_lote.id_lote,far_medicamento_lote.lote,
                IF(far_medicamento_lote.id_bodega=$bodega_pri,'SI','') as lote_pri,
                far_medicamento_lote.fec_vencimiento,far_medicamento_lote.reg_invima,
                far_presentacion_comercial.nom_presentacion,acf_marca.descripcion AS nom_marca,
                ROUND(far_medicamento_lote.existencia/IFNULL(far_presentacion_comercial.cantidad,1),1) AS existencia_umpl,
                far_medicamento_lote.existencia,far_medicamento_cum.cum,
                far_bodegas.nombre AS nom_bodega,                
                IF(far_medicamento_lote.estado=1,'ACTIVO','INACTIVO') AS estado
            FROM far_medicamento_lote
            INNER JOIN far_presentacion_comercial ON (far_presentacion_comercial.id_prescom=far_medicamento_lote.id_presentacion)
            INNER JOIN acf_marca ON (acf_marca.id=far_medicamento_lote.id_marca)
            INNER JOIN far_medicamento_cum ON (far_medicamento_cum.id_cum=far_medicamento_lote.id_cum)
            INNER JOIN far_bodegas ON (far_bodegas.id_bodega=far_medicamento_lote.id_bodega)
            WHERE far_medicamento_lote.id_med=" . $_POST['id_articulo'] . $where . " ORDER BY $col $dir $limit";

    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $editar = NULL;
        $eliminar = NULL;
        $id = $obj['id_lote'];
        //Permite crear botones en la cuadricula si tiene permisos de 3-Editar,4-Eliminar
        if ((PermisosUsuario($permisos, 5002, 3) || $id_rol == 1) && $obj['lote_pri'] == 'SI') {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if ((PermisosUsuario($permisos, 5002, 4) || $id_rol == 1) && $obj['lote_pri'] == 'SI') {
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id_lote" => $id,
            "lote" => $obj['lote'],
            "lote_pri" => $obj['lote_pri'],
            "fec_vencimiento" => $obj['fec_vencimiento'],
            "reg_invima" => $obj['reg_invima'],
            "nom_marca" => $obj['nom_marca'],
            "nom_presentacion" => $obj['nom_presentacion'],
            "existencia_umpl" => $obj['existencia_umpl'],              
            "existencia" => $obj['existencia'],
            "cum" => $obj['cum'],
            "nom_bodega" => $obj['nom_bodega'],
            "estado" => $obj['estado'],
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

   