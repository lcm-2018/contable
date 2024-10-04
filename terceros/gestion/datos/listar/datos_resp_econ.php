<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
$id_t = isset($_POST['id_t']) ? $_POST['id_t'] : exit('Acción no permitida');
//API URL
$url = $api . 'terceros/datos/res/lista/resp_econ/' . $id_t;
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$responsabilidades = json_decode($result, true);
if (!empty($responsabilidades)) {
    foreach ($responsabilidades as $r) {
        $idre = $r['id_resptercero'];
        $estado = $r['estado'] == '1' ? '<span class="fas fa-toggle-on fa-lg activo"></span>' : '<span class="fas fa-toggle-off fa-lg inactivo"></span>';
        $data[] = [
            'codigo' => '<div class="text-center">' . $r['codigo'] . '</div>',
            'descripcion' => mb_strtoupper($r['descripcion']),
            'estado' => '<div class="text-center">' . $estado . '</div>'
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);
