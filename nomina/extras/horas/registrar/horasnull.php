<?php

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
$p = $_POST['p'];
$rs = $_POST['r'];
echo 'Se calcula<input type="number" id="num'.$rs.'CantHe'.$p.'" name="num'.$rs.'CantHe'.$p.'" value="99" hidden>';