<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
$id = isset($_POST['ideps']) ?  $_POST['ideps'] : exit('Acción no permitida');
$_SESSION['del'] = $id;
$res = "Desea eliminar el actual registro: " . $id . "?";

echo $res;
