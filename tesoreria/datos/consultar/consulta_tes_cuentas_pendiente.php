<?php
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_pgcp`.`id_pgcp`
                , `ctb_pgcp`.`cuenta`
                , `ctb_pgcp`.`nombre`
                , `tes_cuentas`.`id_tes_cuenta`
                , `tes_cuentas`.`id_banco`
            FROM
                `tes_cuentas`
                INNER JOIN `ctb_pgcp` 
                    ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
            WHERE `tes_cuentas`.`id_tes_cuenta` IS NULL AND `ctb_pgcp`.`tipo_dato` = 'D' AND(`ctb_pgcp`.`cuenta` LIKE '1132%' OR `ctb_pgcp`.`cuenta` LIKE '1110%')";
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
