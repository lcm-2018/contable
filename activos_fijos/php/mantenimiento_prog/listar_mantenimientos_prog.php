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

$where_gen = " WHERE HV.estado = 3"; //Con estado: 3-En Mantenimiento
$where = $where_gen;
if (isset($_POST['id_mantenimiento']) && $_POST['id_mantenimiento']) {
    $where .= " AND MM.id_mantenimiento='" . $_POST['id_mantenimiento'] . "'";
}
if (isset($_POST['placa']) && $_POST['placa']) {
    $where .= " AND HV.placa='" . $_POST['placa'] . "'";
}
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND FM.nom_medicamento='" . $_POST['nombre'] . "'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND MM.fec_mantenimiento BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['id_tip_man']) && $_POST['id_tip_man']) {
    $where .= " AND MM.tipo_mantenimiento=" . $_POST['id_tip_man'] . "";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND MD.estado=" . $_POST['estado'];
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM acf_mantenimiento_detalle AS MD
            INNER JOIN acf_hojavida AS HV ON (HV.id_activo_fijo=MD.id_activo_fijo)" . $where_gen;
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM acf_mantenimiento_detalle AS MD
            INNER JOIN acf_mantenimiento AS MM ON (MM.id_mantenimiento=MD.id_mantenimiento)
            INNER JOIN acf_hojavida AS HV ON (HV.id_activo_fijo=MD.id_activo_fijo)
            INNER JOIN far_medicamentos FM ON (FM.id_med=HV.id_articulo)" . $where;
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT MD.id_mant_detalle,MM.id_mantenimiento,MM.fec_mantenimiento,	
                CASE MM.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'APROBADO' WHEN 3 THEN 'EN EJECUCION' WHEN 4 THEN 'CERRADO' WHEN 0 THEN 'ANULADO' END AS nom_estado_man,		
                HV.placa,FM.nom_medicamento AS nom_articulo,
                CASE MM.tipo_mantenimiento WHEN 1 THEN 'PREVENTIVO' WHEN 2 THEN 'CORRECTIVO INTERNO' WHEN 3 THEN 'CORRECTIVO EXTERNO' END AS tipo_mantenimiento, 
                MM.fec_ini_mantenimiento,MM.fec_fin_mantenimiento,MD.estado,
                CASE MD.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'EN MANTENIMIENTO' WHEN 3 THEN 'FINALIZADO' END AS nom_estado
            FROM acf_mantenimiento_detalle AS MD
            INNER JOIN acf_mantenimiento AS MM ON (MM.id_mantenimiento=MD.id_mantenimiento)
            INNER JOIN acf_hojavida AS HV ON (HV.id_activo_fijo=MD.id_activo_fijo)
            INNER JOIN far_medicamentos FM ON (FM.id_med=HV.id_articulo)
            $where ORDER BY $col $dir $limit";

    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$editar = NULL;
$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id = $obj['id_mant_detalle'];
        //Permite crear botones en la cuadricula si tiene permisos de 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
        if (PermisosUsuario($permisos, 5706, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Finalizar Mantenimiento"><span class="far fa-check-circle fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5706, 3) || $id_rol == 1) {
            $notas = '<a value="' . $id . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb btn_notas" title="Notas de Mantenimiento"><span class="fas fa-clipboard-list fa-lg"></span></a>';
        }
        $data[] = [
            "id_mant_detalle" => $id,
            "id_mantenimiento" => $obj['id_mantenimiento'],
            "fec_mantenimiento" => $obj['fec_mantenimiento'],
            "nom_estado_man" => $obj['nom_estado_man'],
            "placa" => $obj['placa'],
            "nom_articulo" => $obj['nom_articulo'],
            "tipo_mantenimiento" => $obj['tipo_mantenimiento'],
            "fec_ini_mantenimiento" => $obj['fec_ini_mantenimiento'],
            "fec_fin_mantenimiento" => $obj['fec_fin_mantenimiento'],
            "estado" => $obj['estado'],
            "nom_estado" => $obj['nom_estado'],
            "botones" => '<div class="text-center centro-vertical">' . $editar . $notas . '</div>',
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
