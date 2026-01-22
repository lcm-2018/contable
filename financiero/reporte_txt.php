<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
$data = isset($_POST['txt']) ? $_POST['txt'] : exit('Acción no permitida');
$data = base64_decode($data);
header('Content-type: text/plain');
header('Content-Disposition: attachment; filename=reporte.txt');
echo $data;
