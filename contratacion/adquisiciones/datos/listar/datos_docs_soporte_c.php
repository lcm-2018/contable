<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
$id_ct = isset($_POST['id_csp']) ? $_POST['id_csp'] : exit('Acción no permitida');
//API URL
$url = $api . 'terceros/datos/res/detalles/contrato/' . $id_ct;
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$result = curl_exec($ch);
curl_close($ch);
$det_contrato = json_decode($result, true);
if ($det_contrato != 0) {
    $ruta = base64_encode($det_contrato['contrato']['ruta_contrato'] . $det_contrato['contrato']['nombre_contrato']);
    $num = 1;
    $nom_doc = 'CONTRATO CC-' . $det_contrato['contrato']['id_contrato'];
    $data[] = [
        'num' => $num,
        'doc' => $nom_doc,
        'archivo' => ' <div class="center-block"><a value="' . $ruta . '|' . strtolower($nom_doc) . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb descargar" title="Descargar"><span class="fas fa-file-pdf fa-lg"></span></a></div>',
        'estado' => '<input type="hidden" id ="id_c_final" value="' . $det_contrato['contrato']['id_c_env'] . '">',
    ];
    $num = 2;
    foreach ($det_contrato['docs'] as $ds) {
        $num++;
        $id_doc = $ds['id_doc_c'];
        if ($ds['id_tipo_doc'] == 99) {
            $nom_doc = $ds['otro_tipo'];
        } else {
            $nom_doc = $ds['descripcion'];
        }
        if ($ds['estado'] == 1) {
            $estado = '<span class="fas fa-minus-circle fa-lg shadow-gb rounded-circle" style="color: gray;" title="Pendiente aprobar"></span></a>';
        } else if ($ds['estado'] == 2) {
            $estado = '<span class="fas fa-check-circle fa-lg shadow-gb rounded-circle" style="color:#2ECC71;" title="Aprobado"></span>';
        } else {
            $estado = '<span class="fas fa-times-circle fa-lg shadow-gb rounded-circle" style="color:#E74C3C;" title="Rechazado"></span></a>';
        }
        if ($ds['id_tipo_doc'] == 98) {
            $aux = $num;
            $estado = null;
            $num = 2;
        }
        $ruta = base64_encode($ds['ruta_doc_c'] . $ds['nom_doc_c']);
        $data[] = [
            'num' => $num,
            'doc' => mb_strtoupper($nom_doc),
            'archivo' => '<div class="center-block"><a value="' . $ruta . '|' . strtolower($nom_doc) . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb descargar" title="Descargar"><span class="fas fa-file-pdf fa-lg"></span></a></div>',
            'estado' => '<div class="center-block">' . $estado . '</div>',
        ];
        if ($ds['id_tipo_doc'] == 98) {
            $num = $aux;
        }
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);
