<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';

$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$limit = "";
if ($length != -1){
    $limit = "LIMIT $start, $length";
}
$col = $_POST['order'][0]['column']+1;
$dir = $_POST['order'][0]['dir'];

$where = "WHERE far_subgrupos.id_subgrupo<>0";
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND far_subgrupos.nom_subgrupo LIKE '" . $_POST['nombre'] . "%'";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_subgrupos WHERE id_subgrupo<>0";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM far_subgrupos $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT far_subgrupos.id_subgrupo,far_subgrupos.cod_subgrupo,far_subgrupos.nom_subgrupo,far_grupos.nom_grupo,
                IF(far_subgrupos.lote_xdef=1,'SI','NO') AS lote_xdef,                
                IF(far_subgrupos.estado=1,'ACTIVO','INACTIVO') AS estado
            FROM far_subgrupos
            INNER JOIN far_grupos ON (far_grupos.id_grupo=far_subgrupos.id_grupo)
            $where ORDER BY $col $dir $limit";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$editar = NULL;
$eliminar = NULL;
$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id = $obj['id_subgrupo'];
        /*Permisos del usuario
            5001-Opcion [General][Subgrupos]
            1-Consultar, 2-Adicionar, 3-Modificar, 4-Eliminar, 5-Anular, 6-Imprimir
        */    
        if (PermisosUsuario($permisos, 5001, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5001, 4) || $id_rol == 1) {
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }

        $sql = "SELECT CONCAT_WS(' - ',ctb_pgcp.cuenta,ctb_pgcp.nombre) AS cuenta
                FROM far_subgrupos_cta    
                INNER JOIN ctb_pgcp ON (ctb_pgcp.id_pgcp=far_subgrupos_cta.id_cuenta)            
                WHERE far_subgrupos_cta.estado=1 AND far_subgrupos_cta.fecha_vigencia<=DATE_FORMAT(NOW(), '%Y-%m-%d') AND far_subgrupos_cta.id_subgrupo=$id
                ORDER BY far_subgrupos_cta.fecha_vigencia DESC LIMIT 1";
        $rs = $cmd->query($sql);
        $objs_cta = $rs->fetch();
        $cuenta = isset($objs_cta['cuenta']) ? $objs_cta['cuenta'] : '';

        $data[] = [
            "id_subgrupo" => $id,
            "cod_subgrupo" => $obj['cod_subgrupo'],
            "nom_subgrupo" => mb_strtoupper($obj['nom_subgrupo']),
            "cuenta" => $cuenta,
            "nom_grupo" => mb_strtoupper($obj['nom_grupo']),
            "lote_xdef" => $obj['lote_xdef'],
            "estado" => $obj['estado'],
            "botones" => '<div class="text-center centro-vertical">' . $editar . $eliminar . '</div>',
        ];
    }
}
$cmd = null;
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
