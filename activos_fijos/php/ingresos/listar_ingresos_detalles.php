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
$col = $_POST['order'][0]['column'] + 1;
$dir = $_POST['order'][0]['dir'];

$where = "";
if (isset($_POST['search']['value']) && $_POST['search']['value']){
    $search = $_POST['search']['value'];
    $where .= " AND far_medicamentos.nom_medicamento LIKE '%$search%'";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM acf_orden_ingreso_detalle WHERE id_ingreso=" . $_POST['id_ingreso'];
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total 
            FROM acf_orden_ingreso_detalle 
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med = acf_orden_ingreso_detalle.id_articulo)
            WHERE id_ingreso=" . $_POST['id_ingreso'] . $where; 
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT acf_orden_ingreso_detalle.id_ing_detalle,
                far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,
                acf_orden_ingreso_detalle.cantidad,
                acf_orden_ingreso_detalle.valor_sin_iva,
                acf_orden_ingreso_detalle.iva,
                acf_orden_ingreso_detalle.valor,
                (acf_orden_ingreso_detalle.valor*acf_orden_ingreso_detalle.cantidad) AS val_total,
                acf_orden_ingreso_detalle.observacion
            FROM acf_orden_ingreso_detalle 
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med = acf_orden_ingreso_detalle.id_articulo)
            WHERE acf_orden_ingreso_detalle.id_ingreso=" . $_POST['id_ingreso'] . $where . " ORDER BY $col $dir $limit";

    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$editar = NULL;
$eliminar = NULL;
$editaractivofijo = NULL;
$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id = $obj['id_ing_detalle'];
        //Permite crear botones en la cuadricula si tiene permisos de 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
        if (PermisosUsuario($permisos, 5703, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5703, 3) || $id_rol == 1) {
            $editaractivofijo = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_activofijo" title="Activo Fijo"><span class="fas fa-laptop fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5703, 4) || $id_rol == 1) {
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id_ing_detalle" => $id,
            "cod_medicamento" => $obj['cod_medicamento'],
            "nom_medicamento" => $obj['nom_medicamento'],
            "cantidad" => $obj['cantidad'],
            "valor_sin_iva" => formato_valor($obj['valor_sin_iva']),
            "iva" => $obj['iva'],
            "valor" => formato_valor($obj['valor']),
            "val_total" => formato_valor($obj['val_total']),
            "observacion" => $obj['observacion'],
            "botones" => '<div class="text-center centro-vertical">' . $editar . $editaractivofijo . $eliminar . '</div>',
        ];
    }    
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);

   