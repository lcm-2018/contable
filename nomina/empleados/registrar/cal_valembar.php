<?php

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
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

