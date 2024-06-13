<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Div de acciones de la lista
$id_cuenta = isset($_POST['id_cuenta']) ? $_POST['id_cuenta'] : exit('Acceso no disponible');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_doc`.`fecha`
                , `ctb_fuente`.`cod`
                , `ctb_doc`.`id_manu`
                , `ctb_libaux`.`id_tercero_api`
                , `ctb_libaux`.`debito`
                , `ctb_libaux`.`credito`
                , '--' AS `documento`
                , `ctb_libaux`.`id_ctb_libaux`
            FROM
                `ctb_libaux`
                INNER JOIN `ctb_pgcp` 
                    ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                INNER JOIN `tes_cuentas` 
                    ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                INNER JOIN `ctb_doc` 
                    ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
            WHERE (`tes_cuentas`.`id_tes_cuenta` = $id_cuenta AND `ctb_doc`.`estado` = 2)";
    $rs = $cmd->query($sql);
    $lista = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_t = [];
$terceros = [];
if (!empty($lista)) {
    foreach ($lista as $lp) {
        if ($lp['id_tercero_api'] != '') {
            $id_t[] = $lp['id_tercero_api'];
        }
    }
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

    foreach ($lista as $lp) {
        $check = '<input type="checkbox" name="check[' . $lp['id_ctb_libaux'] . ']" onclick="alert()">';
        $key = array_search($lp['id_tercero_api'], array_column($terceros, 'id_tercero'));
        $nombre = $key !== false ? ltrim($terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['razon_social']) : '---';
        $data[] = [

            'fecha' => date('Y-m-d', strtotime($lp['fecha'])),
            'no_comprobante' => $lp['cod'] . $lp['id_manu'],
            'tercero' => $nombre,
            'documento' => $lp['documento'],
            'debito' => '<div class="text-right">' . pesos($lp['debito']) . '</div>',
            'credito' => '<div class="text-right">' . pesos($lp['credito']) . '</div>',
            'estado' => '<div class="text-center vertical-align-middle">' . $check . '</div>',
        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);

function pesos($valor)
{
    return '$ ' . number_format($valor, 2, ',', '.');
}
