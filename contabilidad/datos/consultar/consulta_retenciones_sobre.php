<?php
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_retencion`, `nombre_retencion` FROM `ctb_retenciones` WHERE `id_retencion_tipo` = 4";
    $rs = $cmd->query($sql);
    $retenciones = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$response = '
<label for="id_rete_sobre" class="small">Sobretasa</label>
<select class="form-control form-control-sm py-0 sm" id="id_rete_sobre" name="id_rete_sobre"  required>
<option value="0">-- Seleccionar--</option>';
foreach ($retenciones as $ret) {
    $response .= '<option value="' . $ret['id_retencion'] . '">' . $ret['nombre_retencion'] .  '</option>';
}
$response .= "</select> <button type='button' class='btn btn-warning btn-sm' onclick='guardaSobretasa();'>+</button>";
echo $response;
exit;
