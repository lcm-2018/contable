<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
$data = isset($_POST['xls']) ? $_POST['xls'] : exit('Acción no permitida');
$data = base64_decode($data);
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

header('Content-type:application/xls');
header('Content-Disposition: attachment; filename=reporte' . $date->format('mdHm') . '.xls');
echo $data;
