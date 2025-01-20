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

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    
    //Consulta el total de registros de la tabla
    $sql = "SELECT COUNT(*) AS total FROM far_subgrupos WHERE id_grupo IN (1,2)";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];
    $totalRecordsFilter = $total['total'];

    //Consulta los datos para listarlos en la tabla
    $sql = "SELECT C.id_cecsubgrp_det,SG.id_subgrupo,SG.nom_subgrupo,C.cuenta
            FROM far_subgrupos AS SG
            LEFT JOIN (SELECT CSG.*,CONCAT_WS(' - ',CTA.cuenta,CTA.nombre) AS cuenta
                    FROM tb_centrocostos_subgr_cta_detalle AS CSG
                    INNER JOIN ctb_pgcp AS CTA ON (CTA.id_pgcp=CSG.id_cuenta)
                    WHERE CSG.id_cecsubgrp=" . $_POST['id_cec_sg'] .") AS C ON (C.id_subgrupo=SG.id_subgrupo)
            WHERE SG.id_grupo IN (1,2)
            ORDER BY $col $dir $limit";

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
        $id = $obj['id_cecsubgrp_det'];
        //Permite crear botones en la cuadricula si tiene permisos de 3-Editar,4-Eliminar
        if (PermisosUsuario($permisos, 5508, 3) || $id_rol == 1) {    
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb btn_editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if (PermisosUsuario($permisos, 5508, 4) || $id_rol == 1) {    
            $eliminar =  '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb btn_eliminar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }        
        $data[] = [
            "id_cecsubgrp_det" => $id,
            "id_subgrupo" => $obj['id_subgrupo'],
            "nom_subgrupo" => $obj['nom_subgrupo'],
            "cuenta" => $obj['cuenta'],
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

   