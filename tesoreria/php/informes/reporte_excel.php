<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
$data = isset($_POST['xls']) ? $_POST['xls'] : exit('Acción no permitida');
$data = base64_decode($data);
header('Content-type:application/xls');
header('Content-Disposition: attachment; filename=reporte_excel.xls');
// para que tome tildes y eñes  
echo "\xEF\xBB\xBF";
echo $data;
