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
   $bus = $_POST['search']['value'];
   $where .= " AND (CONCAT(CACT.cuenta,CACT.nombre) LIKE '%" . $bus . "%' OR CONCAT(CACT.cuenta,CACT.nombre) LIKE '%" . $bus . "%' OR CONCAT(CACT.cuenta,CACT.nombre) LIKE '%" . $bus . "%')";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    
    //Consulta la Cuenta Vigente
    $sql = "SELECT id_subgrupo_cta AS id FROM far_subgrupos_cta_af
            WHERE estado=1 AND fecha_vigencia<=DATE_FORMAT(NOW(), '%Y-%m-%d') AND id_subgrupo=" . $_POST['id_subgrupo'] . " 
            ORDER BY fecha_vigencia DESC LIMIT 1";
    $rs = $cmd->query($sql);
    $cuenta = $rs->fetch();
    $id_vig = isset($cuenta['id']) ? $cuenta['id'] : 0;

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_subgrupos_cta_af WHERE id_subgrupo=" . $_POST['id_subgrupo'];
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total 
            FROM far_subgrupos_cta_af AS SBG
            INNER JOIN ctb_pgcp AS CACT ON (CACT.id_pgcp=SBG.id_cuenta)
            INNER JOIN ctb_pgcp AS CDEP ON (CDEP.id_pgcp=SBG.id_cuenta_dep)
            INNER JOIN ctb_pgcp AS CGAS ON (CGAS.id_pgcp=SBG.id_cuenta_gas)
            WHERE SBG.id_subgrupo=" . $_POST['id_subgrupo'] . $where; 
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT SBG.id_subgrupo_cta,SBG.fecha_vigencia,
                CONCAT_WS(' - ',CACT.cuenta,CACT.nombre) AS cuenta_act,
                CONCAT_WS(' - ',CDEP.cuenta,CDEP.nombre) AS cuenta_dep,
                CONCAT_WS(' - ',CGAS.cuenta,CGAS.nombre) AS cuenta_gas,
                IF(SBG.estado=1,'ACTIVO','INACTIVO') AS estado
            FROM far_subgrupos_cta_af AS SBG
            INNER JOIN ctb_pgcp AS CACT ON (CACT.id_pgcp=SBG.id_cuenta)
            INNER JOIN ctb_pgcp AS CDEP ON (CDEP.id_pgcp=SBG.id_cuenta_dep)
            INNER JOIN ctb_pgcp AS CGAS ON (CGAS.id_pgcp=SBG.id_cuenta_gas)
            WHERE SBG.id_subgrupo=" . $_POST['id_subgrupo'] . $where . " ORDER BY $col $dir $limit";

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
        $id = $obj['id_subgrupo_cta'];
        //Permite crear botones en la cuadricula si tiene permisos de 3-Editar,4-Eliminar
        if (PermisosUsuario($permisos, 5509, 3) || $id_rol == 1) {    
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5509, 4) || $id_rol == 1) {    
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id_subgrupo_cta" => $id,
            "cuenta_act" => $obj['cuenta_act'],
            "cuenta_dep" => $obj['cuenta_dep'],
            "cuenta_gas" => $obj['cuenta_gas'],
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

   