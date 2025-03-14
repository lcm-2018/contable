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
if ($length != -1) {
    $limit = "LIMIT $start, $length";
}
$col = $_POST['order'][0]['column'] + 1;
$dir = $_POST['order'][0]['dir'];

//estos where modificarlos con el filtro para buscar por disponibilidad y rango de fechas
$and_where = "";
/*if (isset($_POST['nrodisponibilidad']) && $_POST['nrodisponibilidad']) {
    $where .= " AND far_medicamentos.cod_medicamento LIKE '" . $_POST['codigo'] . "%'";
}*/
if (isset($_POST['fecini']) && $_POST['fecini'] && isset($_POST['fecfin']) && $_POST['fecfin']) {
    $and_where .= " AND pto_cdp.fecha BETWEEN '" . $_POST['fecini'] . "' AND '" . $_POST['fecfin'] . "'";
}
/*if (isset($_POST['subgrupo']) && $_POST['subgrupo']) {
    $where .= " AND far_medicamentos.id_subgrupo=" . $_POST['subgrupo'];
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND far_medicamentos.estado=" . $_POST['estado'];
}*/
//----------------------------------------------

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
                COUNT(*) AS filas
                ,tb_terceros.id_tercero_api
                , tb_terceros.nit_tercero
                , tb_terceros.nom_tercero
                , pto_cdp.id_manu
                , pto_cdp.id_pto_cdp
                , DATE_FORMAT(pto_cdp.fecha, '%Y-%m-%d') AS fecha
                , pto_cdp.objeto                
                , SUM(pto_cdp_detalle2.valor) AS valor_cdp   
                , SUM(IFNULL(pto_cdp_detalle2.valor_liberado,0)) AS valor_cdp_liberado   
                , SUM(pto_crp_detalle2.valor) AS valor_crp
                , SUM(IFNULL(pto_crp_detalle2.valor_liberado,0)) AS valor_crp_liberado
                , (SUM(pto_cdp_detalle2.valor) - SUM(IFNULL(pto_cdp_detalle2.valor_liberado,0))) - (SUM(pto_crp_detalle2.valor) - SUM(IFNULL(pto_crp_detalle2.valor_liberado,0))) AS saldo
            FROM
                pto_cdp
                INNER JOIN (SELECT id_pto_cdp,SUM(valor) AS valor,SUM(valor_liberado) AS valor_liberado FROM pto_cdp_detalle GROUP BY id_pto_cdp) AS pto_cdp_detalle2 ON (pto_cdp_detalle2.id_pto_cdp = pto_cdp.id_pto_cdp)
            INNER JOIN pto_crp ON (pto_crp.id_cdp = pto_cdp.id_pto_cdp)
                INNER JOIN (SELECT id_pto_crp,SUM(valor) AS valor,SUM(valor_liberado) AS valor_liberado FROM pto_crp_detalle GROUP BY id_pto_crp) AS pto_crp_detalle2 ON (pto_crp_detalle2.id_pto_crp = pto_crp.id_pto_crp)  
            INNER JOIN tb_terceros ON (pto_crp.id_tercero_api = tb_terceros.id_tercero_api)      
            WHERE pto_crp.id_tercero_api=$id_tercero  
             $and_where
            GROUP BY pto_cdp.id_pto_cdp";

    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$totalRecords = 0;
$totalRecordsFilter = 0;

$editar = NULL;
$eliminar = NULL;
$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id_cdp = $obj['id_pto_cdp'];
        $saldo = $obj['saldo'];
        $totalRecords = $obj['filas'];
        $totalRecordsFilter = $obj['filas'];

        /*Permisos del usuario
           5201-Opcion [Terceros][Gestion]
            1-Consultar, 2-Adicionar, 3-Modificar, 4-Eliminar, 5-Anular, 6-Imprimir
            5201 gestion de terceros
            5401 presupuesto gestion
        */
        $liberar = null;
        if (PermisosUsuario($permisos, 5201, 1) || $id_rol == 1 || PermisosUsuario($permisos, 5401, 1)) {
            $listar = '<a value="' . $id_cdp . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_listar" title="Listar"><span class="fas fa-clipboard-list fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5401, 3) || $id_rol == 1) {
            if ($saldo > 0 || $saldo < 0) {
                $liberar =  '<a value="' . $id_cdp . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb btn_liberar" title="Liberar"><span class="fas fa-arrow-alt-circle-left fa-lg"></span></a>';
            }
        }
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
            "botones" => '<div class="text-center centro-vertical">' . $liberar . $listar . '</div>',
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
