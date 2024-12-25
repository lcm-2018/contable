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

$where = " WHERE 1";
if (isset($_POST['id_areori']) && $_POST['id_areori']) {
    $where .= " AND AT.id_area_origen='" . $_POST['id_areori'] . "'";
}
if (isset($_POST['id_resori']) && $_POST['id_resori']) {
    $where .= " AND AT.id_usr_origen='" . $_POST['id_resori'] . "'";
}
if (isset($_POST['id_traslado']) && $_POST['id_traslado']) {
    $where .= " AND AT.id_traslado='" . $_POST['id_traslado'] . "'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND AT.fec_traslado BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['id_aredes']) && $_POST['id_aredes']) {
    $where .= " AND AT.id_area_destino='" . $_POST['id_aredes'] . "'";
}
if (isset($_POST['id_resdes']) && $_POST['id_resdes']) {
    $where .= " AND AT.id_usr_destino='" . $_POST['id_resdes'] . "'";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND AT.estado=" . $_POST['estado'];
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM acf_traslado";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM acf_traslado AS AT $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT AT.id_traslado,AT.fec_traslado,AT.hor_traslado,AT.observaciones,                    
                AO.nom_area AS nom_area_origen,
                CONCAT_WS(' ',UO.apellido1,UO.apellido2,UO.nombre1,UO.nombre2)  AS nom_usuario_origen,                    
                AD.nom_area AS nom_area_destino,
                CONCAT_WS(' ',UD.apellido1,UD.apellido2,UD.nombre1,UD.nombre2)  AS nom_usuario_destino,                
                AT.estado,CASE AT.estado WHEN 0 THEN 'ANULADO' WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' END AS nom_estado 
            FROM acf_traslado AS AT           
            INNER JOIN far_centrocosto_area AS AO ON (AO.id_area = AT.id_area_origen)
            LEFT JOIN seg_usuarios_sistema AS UO ON (UO.id_usuario = AT.id_usr_origen)           
            INNER JOIN far_centrocosto_area AS AD ON (AD.id_area = AT.id_area_destino)
            LEFT JOIN seg_usuarios_sistema AS UD ON (UD.id_usuario = AT.id_usr_destino)
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
        $id = $obj['id_traslado'];
        //Permite crear botones en la cuadricula si tiene permisos de 3-Editar,4-Eliminar
        if (PermisosUsuario($permisos, 5708, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5708, 4) || $id_rol == 1) {
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id_traslado" => $id,
            "fec_traslado" => $obj['fec_traslado'],
            "hor_traslado" => $obj['hor_traslado'],
            "observaciones" => $obj['observaciones'],            
            "nom_area_origen" => mb_strtoupper($obj['nom_area_origen']),
            "nom_usuario_origen" => mb_strtoupper($obj['nom_usuario_origen']),
            "nom_area_destino" => mb_strtoupper($obj['nom_area_destino']),
            "nom_usuario_destino" => mb_strtoupper($obj['nom_usuario_destino']),                     
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
