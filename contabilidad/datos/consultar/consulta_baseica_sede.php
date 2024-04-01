<?php
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
    SUM(`seg_ctb_causa_costos`.`valor`) as base
    , `seg_terceros`.`id_tercero_api`
    FROM
    `tb_sedes`
    INNER JOIN `seg_terceros` 
        ON (`tb_sedes`.`id_tercero` = `seg_terceros`.`id_tercero`)
    INNER JOIN `seg_ctb_causa_costos` 
        ON (`seg_ctb_causa_costos`.`id_sede` = `tb_sedes`.`id_sede`)
    WHERE (`seg_ctb_causa_costos`.`id_ctb_doc` ={$_post['id_doc']})
    GROUP BY `seg_ctb_causa_costos`.`id_sede`;";
    $rs = $cmd->query($sql);
    $retenciones = $rs->fetchAll();
    // buscar valor_total,valor_base,valor_iva de la tabla seg_ctb_factura cuando id_Ctb_doc = $_post['id_doc']
    $sql = "SELECT
    `seg_ctb_factura`.`valor_pago`
    , `seg_ctb_factura`.`valor_base`
    , `seg_ctb_factura`.`valor_iva`
    FROM
    `seg_ctb_factura`
    WHERE (`seg_ctb_factura`.`id_ctb_doc` = {$_post['id_doc']});";
    $rs = $cmd->query($sql);
    $factura = $rs->fetch();
    $valor_total = $factura['valor_pago'];
    $valor_base = $factura['valor_base'];
    $valor_iva = $factura['valor_iva'];
    $base_ica = $valor_base;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$response = '
<div class="btn-group">
<button type="submit" class="btn btn-danger btn-sm" onclick="">-</button>
<select class="form-control form-control-sm py-0 sm" id="id_rete_sede" name="id_rete_sede" required>
<option value="0">-- Seleccionar --</option>';
foreach ($retenciones as $ret) {
    $id_ter = $ret['id_tercero_api'];
    $part = $ret['base'] / $valor_total;
    $valor = $part * $base_ica;
    $valor_p = number_format($valor, 2, ',', '.');
    $id_desc = $ret['id_tercero_api'] . "_" . $valor;
    // Consulto tercero registrado en contratación del api de tercero para mostrar el nombre
    // Consulta terceros en la api ********************************************* API
    $url = $api . 'terceros/datos/res/datos/id/' . $id_ter;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res_api = curl_exec($ch);
    curl_close($ch);
    $dat_ter = json_decode($res_api, true);
    $tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
    // fin api terceros ******************************************************** 
    $response .= '<option value="' . $id_desc  . '">' . $tercero . " " . $valor_p .   '</option>';
}
$response .= '</select> </div>';
echo $response;
exit;
