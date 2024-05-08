<?php
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                SUM(`ctb_causa_costos`.`valor`) AS `base`
                , `tb_sedes`.`id_tercero_api`
            FROM
                `ctb_causa_costos`
                INNER JOIN `far_centrocosto_area` 
                    ON (`ctb_causa_costos`.`id_area_cc` = `far_centrocosto_area`.`id_area`)
                INNER JOIN `tb_sedes` 
                    ON (`far_centrocosto_area`.`id_sede` = `tb_sedes`.`id_sede`)
            WHERE (`ctb_causa_costos`.`id_ctb_doc` = {$_post['id_doc']})
            GROUP BY `tb_sedes`.`id_municipio`";
    $rs = $cmd->query($sql);
    $retenciones = $rs->fetchAll();
    // buscar valor_total,valor_base,valor_iva de la tabla seg_ctb_factura cuando id_Ctb_doc = $_post['id_doc']
    $valores = $_post['valores'];
    $valor_total = $valores[0] + $valores[1];
    $valor_base = $valores[0];
    $valor_iva = $valores[1];
    $base_ica = $valor_base;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_t = [];
foreach ($retenciones as $ret) {
    $id_t[] = $ret['id_tercero_api'];
}
if (!empty($id_t)) {

    $payload = json_encode($id_t);
    //API URL
    $url = $api . 'terceros/datos/res/lista/terceros';
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $terceros = json_decode($result, true);
} else {
    $terceros = [];
}
$response = '
<label for="id_rete_sede" class="small">Municipio</label>
<select class="form-control form-control-sm py-0 sm" id="id_rete_sede" name="id_rete_sede" required>
<option value="0">-- Seleccionar --</option>';
foreach ($retenciones as $ret) {
    $part = $ret['base'] / $valor_total;
    $valor = $part * $base_ica;
    $valor_p = number_format($valor, 2, ',', '.');
    $id_desc = $ret['id_tercero_api'] . "_" . $valor;
    $key = array_search($ret['id_tercero_api'], array_column($terceros, 'id_tercero'));
    if ($key === false) {
        $tercero = '';
    } else {
        $tercero = ltrim($terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['razon_social']);
    }
    $response .= '<option value="' . $id_desc  . '">' . $tercero . " " . $valor_p .   '</option>';
}
$response .= '</select> </div>';
echo $response;
exit;
