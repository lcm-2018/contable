
<?php

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

require_once  $_SESSION['urlin'] . '/vendor/autoload.php';

$options = new QROptions([
    'version' => QRCode::VERSION_AUTO,
    'eccLevel' => QRCode::ECC_L,
    'imageType' => QRCode::OUTPUT_IMAGE_PNG,
]);
$qrcode = new QRCode($options);

// and dump the output
$qrcode->render($data_qr);

$codigo_qr = '<img src="' . $qrcode->render($data_qr) . '" alt="QR Code" />';
