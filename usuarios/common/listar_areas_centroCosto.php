<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../conexion.php';

$idusr = $_SESSION['id_user'];
$idrol = $_SESSION['rol'];
$titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
$id_centroCosto = $_POST['id_centroCosto'];
$todas = isset($_POST['todas']) ? $_POST['todas'] : false;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    
    echo '<option value="">' . $titulo . '</option>';    
    if ($idrol == 1 || $todas){
        $sql = "SELECT far_centrocosto_area.id_area,CONCAT(far_centrocosto_area.nom_area,'-',tb_sedes.nom_sede) as nom_area FROM far_centrocosto_area
                INNER JOIN tb_centrocostos ON (tb_centrocostos.id_centro=far_centrocosto_area.id_centrocosto)
                INNER JOIN tb_sedes ON (tb_sedes.id_sede=far_centrocosto_area.id_sede)
                WHERE far_centrocosto_area.id_centrocosto=$id_centroCosto";
    } 
    /*else {    
        $sql = "SELECT far_bodegas.id_bodega,far_bodegas.nombre FROM far_bodegas
                INNER JOIN tb_sedes_bodega ON (tb_sedes_bodega.id_bodega=far_bodegas.id_bodega)
                INNER JOIN seg_bodegas_usuario ON (seg_bodegas_usuario.id_bodega=far_bodegas.id_bodega AND seg_bodegas_usuario.id_usuario=$idusr)
                WHERE tb_sedes_bodega.id_sede=$idsede";
    }*/
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    foreach ($objs as $obj) {
        echo '<option value="' . $obj['id_area'] . '">' . $obj['nom_area'] . '</option>';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
