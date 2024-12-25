<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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

if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND FM.nom_medicamento LIKE '%" . $_POST['nombre'] . "%'";
}
if (isset($_POST['placa']) && $_POST['placa']) {
    $where .= " AND HV.placa LIKE '" . $_POST['placa'] . "%'";
}
if (isset($_POST['num_serial']) && $_POST['num_serial']) {
    $where .= " AND HV.num_serial LIKE '" . $_POST['num_serial'] . "%'";
}
if (isset($_POST['marca']) && $_POST['marca']) {
    $where .= " AND HV.id_marca=" . $_POST['marca'];
}
if (isset($_POST['estado_gen']) && $_POST['estado_gen']) {
    $where .= " AND HV.estado_general=" . $_POST['estado_gen'];
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND HV.estado=" . $_POST['estado'];
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM acf_hojavida";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM acf_hojavida HV
            INNER JOIN far_medicamentos AS FM On (FM.id_med = HV.id_articulo)
            INNER JOIN acf_marca AS MA ON (MA.id = HV.id_marca) $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT HV.id_activo_fijo,
                FM.cod_medicamento cod_articulo,FM.nom_medicamento nom_articulo,
                HV.placa,HV.num_serial,
                MA.descripcion marca,HV.valor,
                SE.nom_sede,AR.nom_area,
                CONCAT_WS(' ',US.apellido1,US.apellido2,US.nombre1,US.nombre2) AS nom_responsable,
                HV.estado_general,
                CASE HV.estado_general WHEN 1 THEN 'BUENO' WHEN 2 THEN 'REGULAR' WHEN 3 THEN 'MALO' 
                                        WHEN 4 THEN 'SIN SERVICIO' END AS nom_estado_general,
                HV.estado,
                CASE HV.estado WHEN 1 THEN 'ACTIVO' WHEN 2 THEN 'PARA MANTENIMIENTO' WHEN 3 THEN 'EN MANTENIMIENTO'
                                    WHEN 4 THEN 'INACTIVO' WHEN 5 THEN 'DADO DE BAJA' END AS nom_estado
            FROM acf_hojavida HV
            INNER JOIN far_medicamentos FM On (FM.id_med = HV.id_articulo)
            INNER JOIN acf_marca MA ON (MA.id = HV.id_marca)
            LEFT JOIN tb_sedes SE ON (SE.id_sede=HV.id_sede)
            LEFT JOIN far_centrocosto_area AR ON (AR.id_area=HV.id_area)
            LEFT JOIN seg_usuarios_sistema AS US ON (US.id_usuario=HV.id_responsable)
            $where ORDER BY $col $dir $limit";

    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$editar = NULL;
$componente = NULL;
$imagen = NULL;
$archivos = NULL;
$eliminar = NULL;
$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id = $obj['id_activo_fijo'];
        //Permite crear botones en la cuadricula si tiene permisos de 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
        if (PermisosUsuario($permisos, 5704, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }        
        if (PermisosUsuario($permisos, 5704, 3) || $id_rol == 1) {
            $imagen =  '<a value="' . $id . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb btn_imagen" title="Imagen"><span class="fas fa-file-image-o fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5704, 3) || $id_rol == 1) {
            $componente =  '<a value="' . $id . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb btn_componente" title="Componentes"><span class="fas fa-laptop fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5704, 3) || $id_rol == 1) {
            $archivos =  '<a value="' . $id . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb btn_archivos" title="Archivos Anexos"><span class="fas fa-paperclip fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5704, 4) || $id_rol == 1) {
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id" => $id,
            "placa" => $obj['placa'],            
            "cod_articulo" => $obj['cod_articulo'],
            "nom_articulo" => $obj['nom_articulo'],            
            "num_serial" => $obj['num_serial'],
            "marca" => $obj['marca'],
            "valor" => $obj['valor'],            
            "nom_sede" => $obj['nom_sede'],
            "nom_area" => $obj['nom_area'],
            "nom_responsable" => $obj['nom_responsable'],
            "estado_general" => $obj['estado_general'],
            "nom_estado_general" => $obj['nom_estado_general'],
            "estado" => $obj['estado'],
            "nom_estado" => $obj['nom_estado'],
            "botones" => '<div class="text-center centro-vertical">' . $editar . $imagen . $componente . $archivos . $eliminar . '</div>',
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
