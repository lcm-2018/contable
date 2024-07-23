<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';

$usuario = $_SESSION['id_user'];
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_consulta,nom_consulta,des_consulta,consulta,parametros FROM tb_consultas_sql
            WHERE id_consulta=$id";
    $rs = $cmd->query($sql);
    $obj = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

if ($obj){
    $data['id_consulta'] = $obj['id_consulta'];
    $data['nom_consulta'] = $obj['nom_consulta'];
    $data['des_consulta'] = $obj['des_consulta'];
    $data['consulta'] = $obj['consulta'];
    $data['parametros'] = $obj['parametros'];
}

if (empty($data)) {
    $data['id_consulta'] = '';
    $data['nom_consulta'] = 'No hay coincidencias...';
}
echo json_encode($data);
