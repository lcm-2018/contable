<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$idusr = $_SESSION['id_user'];
$idrol = $_SESSION['rol'];
$titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
$idsede = $_POST['id_sede'] != '' ? $_POST['id_sede'] : 0;
$todas = isset($_POST['todas']) ? $_POST['todas'] : false;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    
    echo '<option value="">' . $titulo . '</option>';    
    if ($idrol == 1 || $todas){
        $sql = "SELECT far_bodegas.id_bodega,far_bodegas.nombre FROM far_bodegas
                INNER JOIN tb_sedes_bodega ON (tb_sedes_bodega.id_bodega=far_bodegas.id_bodega)
                WHERE tb_sedes_bodega.id_sede=$idsede
                ORDER BY far_bodegas.es_principal DESC, far_bodegas.nombre";
    } else {    
        $sql = "SELECT far_bodegas.id_bodega,far_bodegas.nombre FROM far_bodegas
                INNER JOIN tb_sedes_bodega ON (tb_sedes_bodega.id_bodega=far_bodegas.id_bodega)
                INNER JOIN seg_bodegas_usuario ON (seg_bodegas_usuario.id_bodega=far_bodegas.id_bodega AND seg_bodegas_usuario.id_usuario=$idusr)
                WHERE tb_sedes_bodega.id_sede=$idsede
                ORDER BY far_bodegas.es_principal DESC, far_bodegas.nombre";
    }

    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    foreach ($objs as $obj) {
        echo '<option value="' . $obj['id_bodega'] . '">' . $obj['nombre'] . '</option>';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
