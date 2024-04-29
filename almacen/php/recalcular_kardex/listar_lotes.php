<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
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
$col = $_POST['order'][0]['column'] ? $_POST['order'][0]['column'] : 1;
$dir = $_POST['order'][0]['dir'];
$idusr = $_SESSION['id_user'];
$idrol = $_SESSION['rol'];

$checked = $_POST['selfil'] == 1 ? "checked" : "";

$where_usr = " WHERE 1";
if($idrol !=1){
    $where_usr .= " AND far_medicamento_lote.id_bodega IN (SELECT id_bodega FROM seg_bodegas_usuario WHERE id_usuario=$idusr)";
}
$where = "";
if (isset($_POST['id_sede']) && $_POST['id_sede']) {
    $where .= " AND far_medicamento_lote.id_bodega IN (SELECT id_bodega FROM tb_sedes_bodega WHERE id_sede=" . $_POST['id_sede'] . ")";
}
if (isset($_POST['id_bodega']) && $_POST['id_bodega']) {
    $where .= " AND far_medicamento_lote.id_bodega='" . $_POST['id_bodega'] . "'";
}

if (isset($_POST['opcion']) && $_POST['opcion']){
    if($_POST['opcion'] == 'O'){ //Opcion datos de Articulo
        if ($_POST['codigo']) {
            $where .= " AND far_medicamentos.cod_medicamento LIKE '" . $_POST['codigo'] . "%'";
        }
        if ($_POST['nombre']) {
            $where .= " AND far_medicamentos.nom_medicamento LIKE '" . $_POST['nombre'] . "%'";
        }
        if ($_POST['fecini']){
            $where .= " AND far_medicamento_lote.id_lote IN (SELECT id_lote FROM far_kardex WHERE fec_movimiento>='" . $_POST['fecini'] . "')";
        }
    } else if($_POST['opcion'] == 'I'){ //Opcion Id. de Orden de Ingreso
        if ($_POST['id_ing']) {
            $where .= " AND far_medicamento_lote.id_lote IN (SELECT id_lote FROM far_orden_ingreso_detalle WHERE id_ingreso=" . $_POST['id_ing'] . ")";
        }    
    } else if($_POST['opcion'] == 'E'){ //Opcion Id. de Orden de Egreso
        if ($_POST['id_egr']) {
            $where .= " AND far_medicamento_lote.id_lote IN (SELECT id_lote FROM far_orden_egreso_detalle WHERE id_egreso=" . $_POST['id_egr'] . ")";
        }    
    } else if($_POST['opcion'] == 'T'){ //Opcion Id. de Orden de Traslado
        if ($_POST['id_tra']) {
            $where .= " AND (far_medicamento_lote.id_lote IN (SELECT id_lote_origen FROM far_traslado_detalle WHERE id_traslado=" . $_POST['id_tra'] . ") 
                        OR far_medicamento_lote.id_lote IN (SELECT id_lote_destino FROM far_traslado_detalle WHERE id_traslado=" . $_POST['id_tra'] . "))";
        }                
    }
} else {
    $where_usr = " WHERE 1=2";    
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_medicamento_lote $where_usr";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total 
        FROM far_medicamento_lote 
        INNER JOIN far_medicamentos ON (far_medicamentos.id_med = far_medicamento_lote.id_med) $where_usr $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT far_medicamentos.id_med,far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,                 
                tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,	
                far_medicamento_lote.id_lote,far_medicamento_lote.lote,far_medicamento_lote.existencia AS existencia_lote,                
                far_medicamentos.cod_medicamento,far_medicamentos.existencia,far_medicamentos.val_promedio  
            FROM far_medicamento_lote
            INNER JOIN far_bodegas ON (far_bodegas.id_bodega = far_medicamento_lote.id_bodega)
            INNER JOIN tb_sedes_bodega ON (tb_sedes_bodega.id_bodega = far_bodegas.id_bodega)
            INNER JOIN tb_sedes ON (tb_sedes.id_sede = tb_sedes_bodega.id_sede)
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med = far_medicamento_lote.id_med)
            $where_usr $where ORDER BY $col $dir $limit";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id = $obj['id_lote'];
        $data[] = [
            "select" => '<input type="checkbox" name="art[]" value="' . $id . '" ' . $checked . '>',
            "id_med" => $id,
            "cod_medicamento" => $obj['cod_medicamento'],
            "nom_medicamento" => mb_strtoupper($obj['nom_medicamento']),            
            "nom_sede" => $obj['nom_sede'],
            "nom_bodega" => $obj['nom_bodega'],
            "id_lote" => $obj['id_lote'],
            "lote" => $obj['lote'],
            "existencia_lote" => $obj['existencia_lote'],
            "cod_medicamento" => $obj['cod_medicamento'],
            "existencia" => $obj['existencia'],
            "val_promedio" => formato_valor($obj['val_promedio']),
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
