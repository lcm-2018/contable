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

$where = " WHERE 1";

if (isset($_POST['id_mantenimiento']) && $_POST['id_mantenimiento']) {
    $where .= " AND M.id_mantenimiento='" . $_POST['id_mantenimiento'] . "'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND M.fec_mantenimiento BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['id_tercero']) && $_POST['id_tercero']) {
    $where .= " AND M.id_tercero=" . $_POST['id_tercero'] . "";
}
if (isset($_POST['id_tipo_mant']) && $_POST['id_tipo_mant']) {
    $where .= " AND M.tipo_mantenimiento=" . $_POST['id_tipo_mant'] . "";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND M.estado=" . $_POST['estado'];
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM acf_mantenimiento AS M";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM acf_mantenimiento AS M $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT	M.id_mantenimiento,M.fec_mantenimiento,M.hor_mantenimiento,
                CASE M.tipo_mantenimiento WHEN 1 THEN 'PREVENTIVO' WHEN 2 THEN 'CORRECTIVO INTERNO' WHEN 3 THEN 'CORRECTIVO EXTERNO' END AS tipo_mantenimiento, 
                M.observaciones,
                CONCAT_WS(' ',U.apellido1,U.apellido2,U.nombre1,U.nombre2) AS nom_responsable,
                T.nom_tercero,M.fec_ini_mantenimiento,M.fec_fin_mantenimiento,M.estado,
                CASE M.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'APROBADO' WHEN 3 THEN 'EN EJECUCION' WHEN 4 THEN 'CERRADO' WHEN 0 THEN 'ANULADO' END AS nom_estado
            FROM acf_mantenimiento AS M
            INNER JOIN tb_terceros T ON (T.id_tercero = M.id_tercero)
            INNER JOIN seg_usuarios_sistema U ON (U.id_usuario = M.id_responsable)
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
        $id = $obj['id_mantenimiento'];
        //Permite crear botones en la cuadricula si tiene permisos de 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
        if (PermisosUsuario($permisos, 5705, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5705, 4) || $id_rol == 1) {
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id_mantenimiento" => $id,
            "fec_mantenimiento" => $obj['fec_mantenimiento'],
            "hor_mantenimiento" => $obj['hor_mantenimiento'],
            "tipo_mantenimiento" => $obj['tipo_mantenimiento'],            
            "observaciones" => $obj['observaciones'],
            "nom_responsable" => $obj['nom_responsable'],
            "nom_tercero" => $obj['nom_tercero'],
            "fec_ini_mantenimiento" => $obj['fec_ini_mantenimiento'],
            "fec_fin_mantenimiento" => $obj['fec_fin_mantenimiento'],
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
