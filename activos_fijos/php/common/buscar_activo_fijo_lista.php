<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../common/funciones_generales.php';

$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$limit = "";
if ($length != -1) {
    $limit = "LIMIT $start, $length";
}
$col = $_POST['order'][0]['column'] + 1;
$dir = $_POST['order'][0]['dir'];

//Estados de Activos: 1-Activo, 2-Para mantenimiento, 3-En Mantenimiento, 4-Inactivo, 5-Dado de Baja
$where_gen = " WHERE 1<>1";
if ($_POST['proceso'] == "mant"){           //mant-Proceso de mantenimiento
    $where_gen = " WHERE HV.estado IN (1,2,3)";

} else if ($_POST['proceso'] == "tras"){   //tras-Proceso de traslado
    $where_gen = " WHERE HV.estado IN (1,2,3,4) AND HV.id_area=" . $_POST['id_area'];

} else if ($_POST['proceso'] == "baja"){   //baja-Proceso de dar de baja
    $where_gen = " WHERE HV.estado IN (4)";
}

$where = $where_gen;
if (isset($_POST['placa']) && $_POST['placa']) {
    $where .= " AND HV.placa LIKE '" . $_POST['placa'] . "%'";
}
if (isset($_POST['codigo']) && $_POST['codigo']) {
    $where .= " AND FM.cod_medicamento LIKE '" . $_POST['codigo'] . "%'";
}
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND (FM.nom_medicamento LIKE '" . $_POST['nombre'] . "%' OR HV.des_activo LIKE '" . $_POST['nombre'] . "%')";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM acf_hojavida AS HV
            INNER JOIN far_medicamentos AS FM ON (FM.id_med = HV.id_articulo)" . $where_gen;
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM acf_hojavida AS HV
            INNER JOIN far_medicamentos AS FM ON (FM.id_med = HV.id_articulo)" . $where;
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT HV.id_activo_fijo,HV.placa,
                FM.cod_medicamento cod_articulo,FM.nom_medicamento nom_articulo,
                HV.des_activo,
                HV.num_serial,MA.descripcion AS nom_marca,HV.valor,
                SE.nom_sede,AR.nom_area,
                CONCAT_WS(' ',US.apellido1,US.apellido2,US.nombre1,US.nombre2) AS nom_responsable,
                CASE HV.estado_general WHEN 1 THEN 'BUENO' WHEN 2 THEN 'REGULAR' WHEN 3 THEN 'MALO'
                            WHEN 4 THEN 'SIN SERVICIO' END AS nom_estado_general,
                CASE HV.estado WHEN 1 THEN 'ACTIVO' WHEN 2 THEN 'PARA MANTENIMIENTO' WHEN 3 THEN 'EN MANTENIMIENTO'
                            WHEN 4 THEN 'INACTIVO' WHEN 5 THEN 'DADO DE BAJA' END AS nom_estado
            FROM acf_hojavida HV
            INNER JOIN far_medicamentos FM ON (FM.id_med = HV.id_articulo)
            INNER JOIN acf_marca MA ON (MA.id = HV.id_marca)
            LEFT JOIN tb_sedes SE ON (SE.id_sede=HV.id_sede)
            LEFT JOIN far_centrocosto_area AR ON (AR.id_area=HV.id_area)
            LEFT JOIN seg_usuarios_sistema AS US ON (US.id_usuario=HV.id_responsable)"            
            . $where . " ORDER BY $col $dir $limit";

    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $data[] = [
            "id_activo_fijo" => $obj['id_activo_fijo'],
            "placa" => $obj['placa'],
            "cod_articulo" => $obj['cod_articulo'],
            "nom_articulo" => $obj['nom_articulo'],
            "des_activo" => $obj['des_activo'],
            "num_serial" => $obj['num_serial'],
            "nom_marca" => $obj['nom_marca'],
            "nom_sede" => $obj['nom_sede'],
            "nom_area" => $obj['nom_area'],
            "nom_responsable" => $obj['nom_responsable'],
            "nom_estado_general" => $obj['nom_estado_general'],
            "nom_estado" => $obj['nom_estado']
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);

   