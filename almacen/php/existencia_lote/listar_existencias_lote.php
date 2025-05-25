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

$where_usr = " WHERE 1";
if($idrol !=1){
    $where_usr .= " AND far_medicamento_lote.id_bodega IN (SELECT id_bodega FROM seg_bodegas_usuario WHERE id_usuario=$idusr)";
}

$where = $where_usr;
if (isset($_POST['id_sede']) && $_POST['id_sede']) {
    $where .= " AND far_medicamento_lote.id_bodega IN (SELECT id_bodega FROM tb_sedes_bodega WHERE id_sede=" . $_POST['id_sede'] . ")";
}
if (isset($_POST['id_bodega']) && $_POST['id_bodega']) {
    $where .= " AND far_medicamento_lote.id_bodega='" . $_POST['id_bodega'] . "'";
}
if (isset($_POST['codigo']) && $_POST['codigo']) {
    $where .= " AND far_medicamentos.cod_medicamento LIKE '" . $_POST['codigo'] . "%'";
}
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND far_medicamentos.nom_medicamento LIKE '" . $_POST['nombre'] . "%'";
}
if (isset($_POST['tipo_asis']) && strlen($_POST['tipo_asis'])) {
    $where .= " AND far_medicamentos.es_clinico=" . $_POST['tipo_asis'];
}
if (isset($_POST['id_subgrupo']) && $_POST['id_subgrupo']) {
    $where .= " AND far_medicamentos.id_subgrupo=" . $_POST['id_subgrupo'];
}
if (isset($_POST['artactivo']) && $_POST['artactivo']) {
    $where .= " AND far_medicamentos.estado=1";
}
if (isset($_POST['lotactivo']) && $_POST['lotactivo']) {
    $where .= " AND far_medicamento_lote.estado=1";
}
if (isset($_POST['conexistencia']) && $_POST['conexistencia']) {
    $where .= " AND far_medicamento_lote.existencia>=1";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_medicamento_lote
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
            $where_usr";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total FROM far_medicamento_lote
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
            $where";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT far_medicamento_lote.id_lote,tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,
                far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,far_subgrupos.nom_subgrupo,
                far_medicamento_lote.lote,far_medicamento_lote.existencia,far_medicamentos.val_promedio,
                (far_medicamento_lote.existencia*far_medicamentos.val_promedio) AS val_total,
                far_medicamento_lote.fec_vencimiento,
	            IF(far_medicamentos.estado=1,'ACTIVO','INACTIVO') AS estado
            FROM far_medicamento_lote
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
            INNER JOIN far_subgrupos ON (far_subgrupos.id_subgrupo=far_medicamentos.id_subgrupo)
            INNER JOIN far_bodegas ON (far_bodegas.id_bodega = far_medicamento_lote.id_bodega)
            INNER JOIN tb_sedes_bodega ON (tb_sedes_bodega.id_bodega = far_bodegas.id_bodega)
            INNER JOIN tb_sedes ON (tb_sedes.id_sede = tb_sedes_bodega.id_sede)
            $where ORDER BY $col $dir $limit";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$examinar = NULL;
$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id = $obj['id_lote'];
        //1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
        if (PermisosUsuario($permisos, 5012, 1) || $id_rol == 1) {
            $examinar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle btn_examinar" title="Tarjeta Kardex"><span class="fa fa-wpforms fa-lg"></span></a>';
        }
        $data[] = [
            "id_lote" => $id,
            "nom_sede" => mb_strtoupper($obj['nom_sede']),
            "nom_bodega" => mb_strtoupper($obj['nom_bodega']),
            "cod_medicamento" => $obj['cod_medicamento'],
            "nom_medicamento" => mb_strtoupper($obj['nom_medicamento']),
            "nom_subgrupo" => mb_strtoupper($obj['nom_subgrupo']),
            "lote" => $obj['lote'],
            "existencia" => $obj['existencia'],
            "val_promedio" => formato_valor($obj['val_promedio']),
            "val_total" => formato_valor($obj['val_total']),
            "fec_vencimiento" => $obj['fec_vencimiento'],
            "estado" => $obj['estado'],
            "botones" => '<div class="text-center centro-vertical">' . $examinar . '</div>',
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
