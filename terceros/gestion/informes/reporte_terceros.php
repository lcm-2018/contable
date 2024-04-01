<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_terceros`.`id_tercero`
                , `seg_terceros`.`id_tercero_api`
                , `seg_terceros`.`tipo_doc`
                , `seg_terceros`.`no_doc`
                , `seg_terceros`.`estado`
                , `tb_tipo_tercero`.`descripcion`
            FROM
                `tb_rel_tercero`
                INNER JOIN `seg_terceros` 
                    ON (`tb_rel_tercero`.`id_tercero_api` = `seg_terceros`.`id_tercero_api`)
                INNER JOIN `tb_tipo_tercero` 
                    ON (`tb_rel_tercero`.`id_tipo_tercero` = `tb_tipo_tercero`.`id_tipo`)";
    $rs = $cmd->query($sql);
    $terEmpr = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_t = [];
foreach ($terEmpr as $l) {
    $id_t[] = $l['id_tercero_api'];
}
$payload = json_encode($id_t);
//API URL
$url = $api . 'terceros/datos/res/lista/reportes';
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$datos = json_decode($result, true);
$head = '';
if (!empty($datos)) {
    foreach ($datos[0] as $key => $value) {
        $head .= '<th>' . utf8_decode($key) . '</th>';
    }
} else {
    echo 'No hay datos para mostrar';
    exit();
}
$tbody = '';
foreach ($datos as $d) {
    $tbody .= '<tr>';
    foreach ($d as $key => $value) {
        $tbody .= '<td>' . utf8_decode($value) . '</td>';
    }
    $tbody .= '</tr>';
}
$tabla = <<<EOT
<table class="table-striped table-bordered table-sm nowrap" style="width:100%">
        <thead>
            <tr>$head</tr>
        </thead>
        <tbody>$tbody</tbody>
    </table>
EOT;
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
header('Content-type:application/xls');
header('Content-Disposition: attachment; filename=reporte' . $date->format('mdHms') . '.xls');
echo $tabla;
