<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
require_once  '../../../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

include '../../../conexion.php';
include '../../../permisos.php';
$id_ser = isset($_POST['id']) ? $_POST['id'] : exit('Accion no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_num_serial`.`id_serial`
                , `seg_num_serial`.`placa`
                , `seg_num_serial`.`num_serial`
                , `ctt_bien_servicio`.`bien_servicio`
            FROM
                `seg_num_serial` 
                INNER JOIN `seg_entra_detalle_activos_fijos`
                    ON(`seg_entra_detalle_activos_fijos`.`id_acfijo` = `seg_num_serial`.`id_activo_fijo`)
                INNER JOIN `ctt_bien_servicio` 
                    ON (`seg_entra_detalle_activos_fijos`.`id_prod` = `ctt_bien_servicio`.`id_b_s`)
            WHERE `seg_num_serial`.`id_serial` = '$id_ser'";
    $rs = $cmd->query($sql);
    $acfijo = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$data_qr  = 'ID: ' . $acfijo['id_serial'] . PHP_EOL;
$data_qr .= 'PLACA: ' . $acfijo['placa'] . PHP_EOL;
$data_qr .= 'SERIAL: ' . $acfijo['num_serial'] . PHP_EOL;
$data_qr .= 'NOMBRE: ' . $acfijo['bien_servicio'] . PHP_EOL;
$data_qr .= 'FECHA: ' . $date->format('Y-m-d H:i:s') . PHP_EOL;

$options = new QROptions([
    'version' => QRCode::VERSION_AUTO,
    'eccLevel' => QRCode::ECC_L,
    'imageType' => QRCode::OUTPUT_IMAGE_PNG,
]);
$qrcode = new QRCode($options);

$codigo_qr = '<img id="PrintQR" src="' . $qrcode->render($data_qr) . '" alt="QR Code" />';
$qr64 = base64_encode($qrcode->render($data_qr));
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">CÓDIGO QR</h5>
        </div>
        <div class="px-2">
            <div id="PrintQR">
                <?php
                if (!empty($acfijo)) {
                    echo $codigo_qr;
                } else {
                    echo '<div class="alert alert-danger" role="alert">
                        Error al obtener datos de activo fijo
                    </div>';
                }
                ?>
            </div>
            <div class="text-center pt-1 pb-3">
                <button id="btnPrintQR" type="button" class="btn btn-primary btn-sm">Imprimir</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
            </div>
        </div>
    </div>
</div>