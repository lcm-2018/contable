<?php
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                seg_ctb_pgcp.id_pgcp
                , seg_ctb_pgcp.cuenta
                , seg_ctb_pgcp.nombre
                , seg_tes_cuentas.id_tes_cuenta
                , seg_tes_cuentas.id_banco
            FROM 
                seg_ctb_pgcp
            LEFT JOIN seg_tes_cuentas ON (seg_tes_cuentas.cta_contable=seg_ctb_pgcp.cuenta)
            WHERE seg_tes_cuentas.id_tes_cuenta IS NULL AND tipo_dato ='D' AND cuenta LIKE '1110%' OR cuenta LIKE '1132%' ;";
    $rs = $cmd->query($sql);
    $retenciones = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$response = '
<select class="form-control form-control-sm py-0 sm" id="cuentas" name="cuentas"  required>
<option value="0">-- Seleccionar --</option>';
foreach ($retenciones as $ret) {
    $response .= '<option value="' . $ret['id_pgcp'] . '">' . $ret['cuenta'] . ' | ' . $ret['nombre'] .  '</option>';
}
$response .= "</select>";
echo $response;
exit;
