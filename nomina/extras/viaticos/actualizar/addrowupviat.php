<?php

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
$c = isset($_POST['consec']) ? $_POST['consec'] : exit('Acción no permitida');
$res = "";
if ($c < 5) {
    $res .= '
        <div class="form-group col-md-3 offset-md-4 py-0">
            <div class="form-group">
                <input type="text" class="form-control" name="txtUpConcepto' . $c . '"  placeholder="Concepto de viático">
            </div>
        </div>
        <div class="form-group col-md-2 py-0">
            <div class="form-group">
                <input type="number" class="form-control" name="numUpValor' . $c . '" placeholder="Valor del viático">
            </div>
        </div>
        <div class="form-group col-md-2 py-0">
            <div class="form-group">
                <input type="date" class="form-control" name="datFecViarUp' . $c . '" value="' . date("Y-m-d") . '">
            </div> 
        </div>';
} else {
    $res = '0';
}
echo $res;
