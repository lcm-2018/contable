<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}

include '../../../../conexion.php';
include '../../../../permisos.php';
//API URL
$ide = isset($_POST['id_emp']) ? $_POST['id_emp'] : '';
$data = $_SESSION['nit_emp'] . '|' . $_SESSION['vigencia'] . '|1' . '|' . $ide;
$url = $api . 'terceros/datos/res/consulta/certificados/' . $data;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$data = [];
$forms220 = json_decode($result, true);
if ($forms220 == 0) {
    $data = [];
} else {
    foreach ($forms220 as $f) {
        $ruta = base64_encode($f['ruta'] . $f['nombre_archivo']);
        $descarga = '<button value="' . $ruta . '" class="btn btn-outline-danger btn-sm btn-circle descargar" title="Descargar"><span class="far fa-file-pdf fa-lg"></span></button>';
        $data[] = [
            'id' => $f['id_certificacion'],
            'doc' => $f['cc_nit'],
            'apellidos' => $f['apellido1'] . ' ' . $f['apellido2'],
            'nombres' => $f['nombre1'] . ' ' . $f['nombre2'],
            'botones' => '<div class="text-center">' . $descarga . '</div>'
        ];
    }
}

$datos = ['data' => $data];

echo json_encode($datos);
