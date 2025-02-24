<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';

$id_tercero = $_POST['id_tercero'];

$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$limit = "";
if ($length != -1){
    $limit = "LIMIT $start, $length";
}
$col = $_POST['order'][0]['column']+1;
$dir = $_POST['order'][0]['dir'];

//estos where modificarlos con el filtro para buscar por disponibilidad y rango de fechas
/*$where = "WHERE far_centrocosto_area.id_area<>0";
if (isset($_POST['nom_area']) && $_POST['nom_area']) {
    $where .= " AND far_centrocosto_area.nom_area LIKE '" . $_POST['nom_area'] . "%'";
}
if (isset($_POST['id_cencosto']) && $_POST['id_cencosto']) {
    $where .= " AND far_centrocosto_area.id_centrocosto=" . $_POST['id_cencosto'];
}
if (isset($_POST['id_sede']) && $_POST['id_sede']) {
    $where .= " AND far_centrocosto_area.id_sede=" . $_POST['id_sede'];
}*/

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    /*$sql = "SELECT COUNT(*) AS total FROM far_centrocosto_area WHERE id_area<>0";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM far_centrocosto_area $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];
    */

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT
            count(*) AS filas
            ,tb_terceros.id_tercero_api
            , tb_terceros.nit_tercero
            , tb_terceros.nom_tercero
            , pto_cdp.id_manu
            , pto_cdp.id_pto_cdp
            , DATE_FORMAT(pto_cdp.fecha, '%Y-%m-%d') AS fecha
            , pto_cdp.objeto
            , SUM(pto_cdp_detalle.valor) AS valor_cdp   
            , SUM(IFNULL(pto_cdp_detalle.valor_liberado,0)) AS valor_cdp_liberado   
            , SUM(pto_crp_detalle.valor) AS valor_crp
            , SUM(IFNULL(pto_crp_detalle.valor_liberado,0)) AS valor_crp_liberado
            , (SUM(pto_cdp_detalle.valor) - SUM(IFNULL(pto_cdp_detalle.valor_liberado,0))) - (SUM(pto_crp_detalle.valor) - SUM(IFNULL(pto_crp_detalle.valor_liberado,0))) AS saldo
        FROM
            pto_cdp_detalle 
            INNER JOIN pto_cdp ON (pto_cdp_detalle.id_pto_cdp = pto_cdp.id_pto_cdp)
            INNER JOIN pto_crp_detalle ON (pto_cdp_detalle.id_pto_cdp_det=pto_crp_detalle.id_pto_cdp_det)    
            INNER JOIN pto_crp ON (pto_crp_detalle.id_pto_crp = pto_crp.id_pto_crp)  
            INNER JOIN tb_terceros ON (pto_crp.id_tercero_api = tb_terceros.id_tercero_api)
        WHERE tb_terceros.id_tercero_api = $id_tercero   
        GROUP BY pto_cdp.id_pto_cdp";

    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$totalRecords=0;
$totalRecordsFilter=0;

$editar = NULL;
$eliminar = NULL;
$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id_cdp = $obj['id_pto_cdp'];
        $totalRecords=$obj['filas'];
        $totalRecordsFilter=$obj['filas'];

        /*Permisos del usuario
           5201-Opcion [Terceros][Gestion]
            1-Consultar, 2-Adicionar, 3-Modificar, 4-Eliminar, 5-Anular, 6-Imprimir
        */ 
        if (PermisosUsuario($permisos, 5201, 1) || $id_rol == 1) {
            $listar = '<a value="' . $id_cdp . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_listar" title="Listar"><span class="fas fa-clipboard-list fa-lg"></span></a>';
        }
        /*if (PermisosUsuario($permisos, 5015, 4) || $id_rol == 1) {
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }*/
        $data[] = [
            "id_tercero_api" => $obj['id_tercero_api'],          
            "nit_tercero" => $obj['nit_tercero'],             
            "nom_tercero" => mb_strtoupper($obj['nom_tercero']),             
            "id_manu" => $obj['id_manu'], 
            "id_pto_cdp" => $id_cdp, 
            "fecha" => $obj['fecha'], 
            "objeto" => mb_strtoupper($obj['objeto']),
            "valor_cdp" => $obj['valor_cdp'],
            "valor_cdp_liberado" => $obj['valor_cdp_liberado'],
            "valor_crp" => $obj['valor_crp'],
            "valor_crp_liberado" => $obj['valor_crp_liberado'],
            "saldo" => $obj['saldo'],
            "botones" => '<div class="text-center centro-vertical">' . $listar .'</div>',
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
