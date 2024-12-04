<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
$data = isset($_POST['xls']) ? $_POST['xls'] : exit('Acción no permitida');
$data = base64_decode($data);
header('Content-type: application/vnd.ms-excel charset=utf-8');
header('Content-Disposition: attachment; filename=reporte_excel.xls');
echo $data;
