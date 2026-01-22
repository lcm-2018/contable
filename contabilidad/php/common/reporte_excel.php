<?php
session_start();
if (!isset($_SESSION['user'])) {
<<<<<<< HEAD
    echo '<script>window.location.replace("../../index.php");</script>';
=======
    header('Location: ../../index.php');
>>>>>>> d750d9bf66c1ebfb0ab684f97d76cc2d83a9799b
    exit();
}
$data = isset($_POST['xls']) ? $_POST['xls'] : exit('Acción no permitida');
$data = base64_decode($data);
header('Content-type:application/xls');
header('Content-Disposition: attachment; filename=reporte_excel.xls');
echo $data;
