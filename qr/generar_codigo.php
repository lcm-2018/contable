<?php
session_start();
// Contenido de texto del QRCode
$data = 'http://www.udenar.edu.co';
// Tamaño de QRCode
$size = '500x500';
// Obten la imagen del código QR de la API de Google Chart
// http://code.google.com/apis/chart/infographics/docs/qr_codes.html
$QR = imagecreatefrompng('https://chart.googleapis.com/chart?cht=qr&chld=H|1&chs=' . $size . '&chl=' . urlencode($data));

// EMPEZAR A DIBUJAR LA IMAGEN EN EL CÓDIGO QR
$QR_width = imagesx($QR);
$QR_height = imagesy($QR);

header("Content-type: image/png");

$logo = imagecreate(10, 10);
imagecopyresampled($QR, $logo, $QR_width / 3, $QR_height / 3, 0, 0, 10, 10, 10, 10);

// END OF DRAW

/**
 * Como este ejemplo es un ejemplo simple de PHP, devuelva
 * una respuesta de imagen.
 *
 * Nota: puede guardar la imagen si lo desea.
 */
header('Content-type: image/png');
imagepng($QR);

//imagedestroy($QR);

// Si decide guardar la imagen en algún lugar, elimine el encabezado y use en su lugar:
// $savePath = "/path/to-my-server-images/myqrcodewithlogo.png";
// imagepng($QR, $savePath);
