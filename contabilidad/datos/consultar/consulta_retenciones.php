<?php
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_retencion, nombre_retencion FROM seg_ctb_retenciones WHERE id_retencion_tipo={$_post['id']} ORDER BY nombre_retencion ASC";
    $rs = $cmd->query($sql);
    $retenciones = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$response = '
<select class="form-control form-control-sm py-0 sm" id="id_rete" name="id_rete" onchange="aplicaDescuentoRetenciones(value);" required>
<option value="0">-- Seleccionar --</option>';
foreach ($retenciones as $ret) {
    $response .= '<option value="' . $ret['id_retencion'] . '">' . $ret['nombre_retencion'] .  '</option>';
}
$response .= "</select>";
echo $response;
exit;
