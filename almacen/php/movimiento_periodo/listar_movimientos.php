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
    $where_usr .= " AND far_kardex.id_bodega IN (SELECT id_bodega FROM seg_bodegas_usuario WHERE id_usuario=$idusr)";
}

if($_POST['fecini'] && $_POST['fecfin']){
    $fecini = $_POST['fecini'];
    $fecfin = $_POST['fecfin'];
} else {
    $fecini = date('Y-m-d');
    $fecfin = date('Y-m-d');
}    

$where_kar = $where_usr . " AND far_kardex.estado=1";
if (isset($_POST['id_sede']) && $_POST['id_sede']) {
    $where_kar .= " AND far_kardex.id_sede='" . $_POST['id_sede'] . "'";    
}
if (isset($_POST['id_bodega']) && $_POST['id_bodega']) {
    $where_kar .= " AND far_kardex.id_bodega='" . $_POST['id_bodega'] . "'";
}

$where_mov = $where_kar . ' AND (id_ingreso IS NOT NULL OR id_egreso IS NOT NULL)';

$where_art = " WHERE 1";
if (isset($_POST['codigo']) && $_POST['codigo']) {
    $where_art .= " AND far_medicamentos.cod_medicamento LIKE '" . $_POST['codigo'] . "%'";
}
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where_art .= " AND far_medicamentos.nom_medicamento LIKE '" . $_POST['nombre'] . "%'";
}
if (isset($_POST['tipo_asis']) && strlen($_POST['tipo_asis'])) {
    $where_art .= " AND far_medicamentos.es_clinico=" . $_POST['tipo_asis'];
}
if (isset($_POST['id_subgrupo']) && $_POST['id_subgrupo']) {
    $where_art .= " AND far_medicamentos.id_subgrupo=" . $_POST['id_subgrupo'];
}
if (isset($_POST['artactivo']) && $_POST['artactivo']) {
    $where_art .= " AND far_medicamentos.estado=1";
}
if (isset($_POST['conexistencia']) && $_POST['conexistencia']) {
    $where_art .= " AND ef.existencia_fin>=1";
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total
            FROM far_medicamentos
            INNER JOIN (SELECT id_med FROM far_kardex
                        WHERE id_kardex IN (SELECT MAX(id_kardex) FROM far_kardex $where_usr GROUP BY id_lote)                        
                        GROUP BY id_med	
                        ) AS ef ON (ef.id_med = far_medicamentos.id_med)";        
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];

    //Consulta el total de registros aplicando el filtro
    $sql = "SELECT COUNT(*) AS total
            FROM far_medicamentos
            INNER JOIN far_subgrupos ON (far_subgrupos.id_subgrupo=far_medicamentos.id_subgrupo)
            INNER JOIN (SELECT id_med,SUM(existencia_lote) AS existencia_fin FROM far_kardex
                        WHERE id_kardex IN (SELECT MAX(id_kardex) FROM far_kardex $where_kar AND fec_movimiento<='$fecfin' GROUP BY id_lote)                        
                        GROUP BY id_med	
                        ) AS ef ON (ef.id_med = far_medicamentos.id_med)	
            $where_art";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT far_medicamentos.id_med,far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,
                far_subgrupos.nom_subgrupo,
                IFNULL(ef.existencia_fin,0) as existencia_fin,
                (ef.existencia_fin*vf.val_promedio_fin) AS valores_fin,
                IFNULL(ei.existencia_ini,0) as existencia_ini,
                (ei.existencia_ini*vi.val_promedio_ini) AS valores_ini,
                IFNULL(es.cantidad_ent,0) as cantidad_ent,
                es.valores_ent,
                IFNULL(es.cantidad_sal,0) as cantidad_sal,
                es.valores_sal
            FROM far_medicamentos
            INNER JOIN far_subgrupos ON (far_subgrupos.id_subgrupo=far_medicamentos.id_subgrupo)

            INNER JOIN (SELECT id_med,SUM(existencia_lote) AS existencia_fin FROM far_kardex
                        WHERE id_kardex IN (SELECT MAX(id_kardex) FROM far_kardex $where_kar AND fec_movimiento<='$fecfin' GROUP BY id_lote)                        
                        GROUP BY id_med	
                        ) AS ef ON (ef.id_med = far_medicamentos.id_med)	
            INNER JOIN (SELECT id_med,val_promedio AS val_promedio_fin FROM far_kardex
                        WHERE id_kardex IN (SELECT MAX(id_kardex) FROM far_kardex				
                                            WHERE fec_movimiento<='$fecfin' AND estado=1 
                                            GROUP BY id_med)
                        ) AS vf ON (vf.id_med = far_medicamentos.id_med) 
            
            LEFT JOIN (SELECT id_med,SUM(existencia_lote) AS existencia_ini FROM far_kardex
                        WHERE id_kardex IN (SELECT MAX(id_kardex) FROM far_kardex $where_kar AND fec_movimiento<'$fecini' GROUP BY id_lote)                        
                        GROUP BY id_med	
                        ) AS ei ON (ei.id_med = far_medicamentos.id_med)	
            LEFT JOIN (SELECT id_med,val_promedio AS val_promedio_ini FROM far_kardex
                        WHERE id_kardex IN (SELECT MAX(id_kardex) FROM far_kardex				
                                            WHERE fec_movimiento<'$fecini' AND estado=1 
                                            GROUP BY id_med)
                        ) AS vi ON (vi.id_med = far_medicamentos.id_med) 
            
            LEFT JOIN (SELECT id_med, 
                        SUM(can_ingreso) AS cantidad_ent,SUM(can_ingreso*val_ingreso) AS valores_ent, 
                        SUM(can_egreso) AS cantidad_sal,SUM(can_egreso*val_promedio) AS valores_sal 
                        FROM far_kardex $where_mov AND fec_movimiento BETWEEN '$fecini' AND '$fecfin' AND estado=1 
                        GROUP BY id_med
                        ) AS es ON (es.id_med = far_medicamentos.id_med) 

            $where_art ORDER BY $col $dir $limit";

    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$data = [];
if (!empty($objs)) {
    foreach ($objs as $obj) {
        $id = $obj['id_med'];
        $data[] = [
            "id_med" => $id,
            "cod_medicamento" => $obj['cod_medicamento'],
            "nom_medicamento" => mb_strtoupper($obj['nom_medicamento']),
            "nom_subgrupo" => mb_strtoupper($obj['nom_subgrupo']),
            "existencia_ini" => $obj['existencia_ini'],
            "valores_ini" => formato_valor($obj['valores_ini']),
            "cantidad_ent" => $obj['cantidad_ent'],
            "valores_ent" => formato_valor($obj['valores_ent']),
            "cantidad_sal" => $obj['cantidad_sal'],
            "valores_sal" => formato_valor($obj['valores_sal']),
            "existencia_fin" => $obj['existencia_fin'],
            "valores_fin" => formato_valor($obj['valores_fin']),
        ];
    }
}
$datos = [
    "data" => $data,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecordsFilter
];

echo json_encode($datos);
