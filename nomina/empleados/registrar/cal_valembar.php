<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$v = $_POST['val'];
$s = $_POST['sal'];
if($v === '' || $v <='0'){
    echo '';
}else{
    $p = floatval(($v*$s)/100);
    echo round($p,3);
}

