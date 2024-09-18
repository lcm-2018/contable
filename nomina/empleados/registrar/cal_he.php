<?php

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../../conexion.php';
$p = isset($_POST['tip']) ? $_POST['tip'] : exit('Acción no permitida');
$inicia = $_POST['fli'] . '' . $_POST['hei'];
$termina = $_POST['flf'] . '' . $_POST['hef'];
$apertura = new DateTime($inicia);
$cierre = new DateTime($termina);
$tiempo = $apertura->diff($cierre);
$horas = intval($tiempo->format('%H'));
$minutos = intval($tiempo->format('%I'));
if ($tiempo->d >= 1) {
    $dah = 24 * $tiempo->d;
} else {
    $dah = 0;
}
$tothor = round($dah + $horas + ($minutos / 60), 2);

if ($tiempo->invert === 1) {
    echo '0';
} else {
    echo $tothor . '<input type="number" id="numCantHe' . $p . '" name="numCantHe' . $p . '" value="' . $tothor . '" hidden>';
}
