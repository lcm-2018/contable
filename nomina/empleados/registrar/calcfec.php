<?php

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$inicia = $_POST['inicio'];
$termina = $_POST['fin'];
$actualiza = $_POST['up'];
$tip = $_POST['tip'];
$apertura = new DateTime($inicia);
$cierre = new DateTime($termina);
$tiempo = $apertura->diff($cierre);
$dias = intval($tiempo->format('%d')) + 1;
if ($tiempo->invert === 1) {
    echo '0';
} else {
    if ($tiempo->m >= 1) {
        $mxdia = 30 * $tiempo->m;
    } else {
        $mxdia = 0;
    }
    $dias = $dias + $mxdia;
    if ($actualiza === 'Up') {
        echo $dias . '<input type="number" id="numUpCantDias' . $tip . '" name="numUpCantDias' . $tip . '" value="' . $dias . '" hidden>';
    } else {
        echo $dias . '<input type="number" id="numCantDias' . $tip . '" name="numCantDias' . $tip . '" value="' . $dias . '" hidden>';
    }
}