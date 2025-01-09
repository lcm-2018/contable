<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
include '../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_tercero_api`, `nit_tercero`, `nom_tercero`
            FROM
                `tb_terceros`";
    $rs = $cmd->query($sql);
    $terEmpr = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_t = [];
foreach ($terEmpr as $l) {
    if ($l['id_tercero_api'] > 0) {
        $id_t[] = $l['id_tercero_api'];
    }
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
        $head .= '<th>' . mb_convert_encoding($key, 'UTF-8', 'ISO-8859-1') . '</th>';
    }
} else {
    echo 'No hay datos para mostrar';
    exit();
}
$tbody = '';
foreach ($datos as $d) {
    $tbody .= '<tr>';
    foreach ($d as $key => $value) {
        $tbody .= '<td>' . mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1') . '</td>';
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
