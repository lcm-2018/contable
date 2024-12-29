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
   $where .= " AND CONCAT(ctb_pgcp.cuenta,ctb_pgcp.nombre) LIKE '%" . $_POST['search']['value'] . "%'";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    
    //Consulta la Cuanta Vigente
    $sql = "SELECT id_cec_cta AS id FROM tb_centrocostos_cta
	        WHERE estado=1 AND fecha_vigencia<=DATE_FORMAT(NOW(), '%Y-%m-%d') AND id_cencos=" . $_POST['id_cencos'] . " 
            ORDER BY fecha_vigencia DESC LIMIT 1";
    $rs = $cmd->query($sql);
    $cuenta = $rs->fetch();
    $id_vig = isset($cuenta['id']) ? $cuenta['id'] : 0;

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM tb_centrocostos_cta WHERE id_cencos=" . $_POST['id_cencos'];
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM tb_centrocostos_cta 
            INNER JOIN ctb_pgcp ON (ctb_pgcp.id_pgcp=tb_centrocostos_cta.id_cuenta)
            WHERE id_cencos=" . $_POST['id_cencos'] . $where; 
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT tb_centrocostos_cta.id_cec_cta,
                tb_centrocostos_cta.fecha_vigencia,   
                CONCAT_WS(' - ',ctb_pgcp.cuenta,ctb_pgcp.nombre) AS cuenta,             
                IF(tb_centrocostos_cta.estado=1,'ACTIVO','INACTIVO') AS estado
            FROM tb_centrocostos_cta
            INNER JOIN ctb_pgcp ON (ctb_pgcp.id_pgcp=tb_centrocostos_cta.id_cuenta)
            WHERE tb_centrocostos_cta.id_cencos=" . $_POST['id_cencos'] . $where . " ORDER BY $col $dir $limit";

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
        $id = $obj['id_cec_cta'];
        //Permite crear botones en la cuadricula si tiene permisos de 3-Editar,4-Eliminar
        if (PermisosUsuario($permisos, 5508, 3) || $id_rol == 1) {    
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5508, 4) || $id_rol == 1) {    
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id_cec_cta" => $id,
            "cuenta" => $obj['cuenta'],
            "fecha_vigencia" => $obj['fecha_vigencia'],
            "vigente" => ($id == $id_vig ? 'X' : ''),
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

   