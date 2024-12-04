<?php

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$v = $_POST['val'];
$s = $_POST['sal'];
if($v === ''){
    $p = 0;
}else{
    $p = ($v*100)/$s;
}
echo round($p,3);