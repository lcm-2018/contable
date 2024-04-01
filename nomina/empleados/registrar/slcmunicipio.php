<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';

$iddpto = $_POST['dpto'];
$res = "";
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM tb_municipios WHERE id_departamento = '$iddpto' ORDER BY nom_municipio";
    $rs = $cmd->query($sql);
    $municipios = $rs->fetchAll();
    if($municipios){
        $res.='<option value="0">--Elegir Municipio--</option>';
        foreach($municipios as $m){
            $res.='<option value="' . $m['id_municipio'] . '">' . $m['nom_municipio'] . '</option>'; 
        }
    }else{
        $res = print_r($cmd->errorInfo());
    }
    $cmd = null;
} catch (PDOException $e) {
    $res = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo $res;
