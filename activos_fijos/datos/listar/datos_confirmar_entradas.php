<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$id_c = isset($_POST['ids_confentradas']) ? $_POST['ids_confentradas'] : exit('Acción no permitida');
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
//API URL
$url = $api . 'terceros/datos/res/lista/compra_entregado/' . $id_c;
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$compra_entregada = json_decode($result, true);
$contar = 0;
if (!empty($compra_entregada)) {
    $cadena = explode('|', $_POST['ids_confentradas']);
    $entrega = $cadena[3];
    $recibido = $compra_entregada['entregas'];
    $fec_min = substr($recibido[0]['fec_reg'], 0, 10);
    foreach ($compra_entregada['listado'] as $cel) {
        $id_bs = $cel['id_cot_ter'];
        $j = '1';
        foreach ($recibido as $rc) {
            if ($rc['id_val_cot'] == $cel['id_val_cot']) {
                if ($j == $entrega) {
                    $cantdad = $rc['cantidad_entrega'];
                    $estdo = $rc['estado'];
                    $id_entrega = $rc['id_entrega'];
                }
                $j++;
            }
        }
        $valor = pesos($cel['valor']);
        if ($estdo == 0) {
            $status = 'PENDIENTE';
            $color = 'gray';
            $btnrecp = '<button class="btn btn-outline-success btn-sm btn-circle shadow-gb recepcionar"><span class="fas fa-cart-arrow-down fa-lg"></span></button>';
        } else {
            $status = 'RECEPCIONADO';
            $color = 'green';
            $btnrecp = null;
        }
        if ($cantdad > 0) {
            $data[] = [
                'id_prod' => $cel['id_bn_sv'],
                'bnsv' => $cel['bien_servicio'],
                'id_api' => $id_entrega,
                'cant_act' => '<div class="text-center">' . $cantdad . '</div>',
                'precio' => '<div class="text-right">' . $valor . '</div>',
                'fec_venc' => $fec_min,
                'estado' => '<div class="text-center" style="color: ' . $color . '">' . $status . '</div>',
                'botones' => '<div class="text-center">' . $btnrecp . '</div>',
            ];
            if ($btnrecp != null) {
                $contar++;
            }
        }
    }
    if ($contar == 1) {
        $key = array_key_last($data);
        $data[$key]['botones'] = '<div class="text-center">' . $btnrecp . '</div><input type="hidden" id="comp_cant" value="1">';
    }
} else {
    $data = [];
}
$datos = ['data' => $data];
echo json_encode($datos);
