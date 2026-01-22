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
$idusr = $_SESSION['id_user'];
$idrol = $_SESSION['rol'];

$where_usr = " WHERE far_pedido.es_pedido_spsr=0";
if($idrol !=1){
    $where_usr .= " AND far_pedido.id_bodega_destino IN (SELECT id_bodega FROM seg_bodegas_usuario WHERE id_usuario=$idusr)";
}
$where = "";
if (isset($_POST['id_sedsol']) && $_POST['id_sedsol']) {
    $where .= " AND far_pedido.id_sede_destino='" . $_POST['id_sedsol'] . "'";
}
if (isset($_POST['id_bodsol']) && $_POST['id_bodsol']) {
    $where .= " AND far_pedido.id_bodega_destino='" . $_POST['id_bodsol'] . "'";
}
if (isset($_POST['id_pedido']) && $_POST['id_pedido']) {
    $where .= " AND far_pedido.id_pedido='" . $_POST['id_pedido'] . "'";
}
if (isset($_POST['num_pedido']) && $_POST['num_pedido']) {
    $where .= " AND far_pedido.num_pedido='" . $_POST['num_pedido'] . "'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND far_pedido.fec_pedido BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['id_sedpro']) && $_POST['id_sedpro']) {
    $where .= " AND far_pedido.id_sede_origen='" . $_POST['id_sedpro'] . "'";
}
if (isset($_POST['id_bodpro']) && $_POST['id_bodpro']) {
    $where .= " AND far_pedido.id_bodega_origen='" . $_POST['id_bodpro'] . "'";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND far_pedido.estado=" . $_POST['estado'];
}
if (isset($_POST['modulo']) && strlen($_POST['modulo'])) {
    $where .= " AND far_pedido.creado_far=" . $_POST['modulo'];
}


try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_pedido $where_usr";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM far_pedido $where_usr $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT far_pedido.id_pedido,far_pedido.num_pedido,
                far_pedido.fec_pedido,far_pedido.hor_pedido,far_pedido.detalle,                    
                SSOL.nom_sede AS nom_sede_solicita,BSOL.nombre AS nom_bodega_solicita,                    
                SPRO.nom_sede AS nom_sede_provee,BPRO.nombre AS nom_bodega_provee,                    
                far_pedido.val_total,far_pedido.estado,
                CASE far_pedido.estado WHEN 0 THEN 'ANULADO' WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CONFIRMADO' WHEN 3 THEN 'FINALIZADO' END AS nom_estado,
                PEDIDO.traslados
            FROM far_pedido             
            INNER JOIN tb_sedes AS SSOL ON (SSOL.id_sede = far_pedido.id_sede_destino)
            INNER JOIN far_bodegas AS BSOL ON (BSOL.id_bodega = far_pedido.id_bodega_destino)           
            INNER JOIN tb_sedes AS SPRO ON (SPRO.id_sede = far_pedido.id_sede_origen)
            INNER JOIN far_bodegas AS BPRO ON (BPRO.id_bodega = far_pedido.id_bodega_origen)
            LEFT JOIN (SELECT PPD.id_pedido,GROUP_CONCAT(DISTINCT TT.id_traslado) AS traslados
                        FROM far_traslado_detalle AS TTD
                        INNER JOIN far_traslado AS TT ON (TT.id_traslado=TTD.id_traslado)
                        INNER JOIN far_pedido_detalle AS PPD ON (PPD.id_ped_detalle=TTD.id_ped_detalle)
                        WHERE TT.estado<>0 
                        GROUP BY PPD.id_pedido
                        ) AS PEDIDO ON (PEDIDO.id_pedido=far_pedido.id_pedido)
            $where_usr $where ORDER BY $col $dir $limit";

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
        $id = $obj['id_pedido'];
        //Permite crear botones en la cuadricula si tiene permisos de 3-Editar,4-Eliminar
        if (PermisosUsuario($permisos, 5003, 3) || $id_rol == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5003, 4) || $id_rol == 1) {
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        $data[] = [
            "id_pedido" => $id,
            "num_pedido" => $obj['num_pedido'],
            "fec_pedido" => $obj['fec_pedido'],
            "hor_pedido" => $obj['hor_pedido'],
            "detalle" => $obj['detalle'],            
            "nom_sede_solicita" => mb_strtoupper($obj['nom_sede_solicita']),
            "nom_bodega_solicita" => mb_strtoupper($obj['nom_bodega_solicita']),
            "nom_sede_provee" => mb_strtoupper($obj['nom_sede_provee']),
            "nom_bodega_provee" => mb_strtoupper($obj['nom_bodega_provee']),                     
            "val_total" => formato_valor($obj['val_total']),  
            "estado" => $obj['estado'],
            "nom_estado" => $obj['nom_estado'],
            "traslados" => $obj['traslados'],
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
