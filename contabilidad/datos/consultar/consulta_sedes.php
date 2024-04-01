<?php
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);
// Buscamos si hay registros asocidos al municipio
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_sede, nombre FROM tb_sedes WHERE id_municipio =$_post[id]";
    $rs = $cmd->query($sql);
    $sedes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$response = '<select class="form-control form-control-sm py-0 sm" id="id_sede" name="id_sede" onchange="mostrarCentroCostos(value);">
<option value="">-- Seleccionar --</option>';
foreach ($sedes as $sed) {
    $response .= '<option value="' . $sed['id_sede'] . '">' . $sed['nombre'] .  '</option>';
}
$response .= "</select>";
echo $response;
exit;
