<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$ruta = isset($_POST['id_manual']) ? base64_decode($_POST['id_manual']) : exit('Acción no permitida');
$ruta = '../' . $ruta;

$nomfile = basename($ruta);

header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=$nomfile");
readfile($ruta);
