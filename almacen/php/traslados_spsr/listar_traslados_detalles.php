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
    $search = $_POST['search']['value'];
    $where .= " AND (far_medicamentos.nom_medicamento LIKE '%$search%' OR far_medicamento_lote.lote LIKE '%$search%')";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_traslado_r_detalle WHERE id_traslado=" . $_POST['id_traslado'];
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total 
            FROM far_traslado_r_detalle 
            INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote = far_traslado_r_detalle.id_lote_origen)
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med = far_medicamento_lote.id_med)
            WHERE id_traslado=" . $_POST['id_traslado'] . $where; 
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT far_traslado_r_detalle.id_tra_detalle,
	            far_medicamentos.cod_medicamento,
                CONCAT(far_medicamentos.nom_medicamento,IF(far_medicamento_lote.id_marca=0,'',CONCAT(' - ',acf_marca.descripcion))) AS nom_medicamento,
                far_medicamento_lote.lote,far_medicamento_lote.existencia,far_medicamento_lote.fec_vencimiento,
	            far_traslado_r_detalle.cantidad,far_traslado_r_detalle.valor,
	            far_traslado_r_detalle.valor*far_traslado_r_detalle.cantidad AS val_total
            FROM far_traslado_r_detalle
            INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote = far_traslado_r_detalle.id_lote_origen)
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med = far_medicamento_lote.id_med)
            INNER JOIN acf_marca ON (acf_marca.id=far_medicamento_lote.id_marca)
            WHERE far_traslado_r_detalle.id_traslado=" . $_POST['id_traslado'] . $where . " ORDER BY $col $dir $limit";

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
        $id = $obj['id_tra_detalle'];
        //Permite crear botones en la cuadricula si tiene permisos de 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
        if (PermisosUsuario($permisos, 5017, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5017, 4) || $id_rol == 1) {
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id_tra_detalle" => $id,
            "cod_medicamento" => $obj['cod_medicamento'],
            "nom_medicamento" => $obj['nom_medicamento'],
            "lote" => $obj['lote'],
            "existencia" => $obj['existencia'],
            "fec_vencimiento" => $obj['fec_vencimiento'],
            "cantidad" => $obj['cantidad'],
            "valor" => formato_valor($obj['valor']),
            "val_total" => formato_valor($obj['val_total']),
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

   