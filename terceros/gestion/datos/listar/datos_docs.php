<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';

$id_t = isset($_POST['id_t']) ? $_POST['id_t'] : exit('Acción no permitida');
//API URL
$url = $api . 'terceros/datos/res/listar/docs/' . $id_t;
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$docs = json_decode($result, true);
if ($docs !== '0') {
    foreach ($docs as $d) {
        $id_doc = $d['id_docster'];
        if ($d['fec_vig'] > date('Y-m-d')) {
            $estado = '<span class="fas fa-toggle-on fa-lg estado activo" ></span>';
        } else {
            $estado = '<span class="fas fa-toggle-off fa-lg estado inactivo"></span>';
        }
        $data[] = [
            'id_doc' => $id_doc,
            'tipo' => mb_strtoupper($d['descripcion']),
            'fec_inicio' => $d['fec_inicio'],
            'fec_vigencia' => $d['fec_vig'],
            'vigente' => '<div class="text-center">' . $estado . '</div>',
            'doc' => '<div class="text-center"><a value="' . $id_doc . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb descargar" title="Descargar"><span class="far fa-file-pdf fa-lg"></span></a></div>',
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);
