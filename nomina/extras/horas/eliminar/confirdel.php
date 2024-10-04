<?php

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
$id = isset($_POST['idhoext']) ? $_POST['idhoext'] : exit('Acción no permitida');
$_SESSION['del'] = $id;
$res = "Desea eliminar el actual registro: " . $id . "?";

echo $res;
