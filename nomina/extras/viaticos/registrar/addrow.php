<?php

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
$c = $_POST['consec'];
$val = $_POST['valxdia'];
$res = "";
if ($c < 20) {
    $day = $c + 1;
    $res .= '
        <div class="form-group col-md-1 text-center">
            <div class="input-group-text form-control center-block">
                <input type="checkbox" checked name="checkP' . $c. '" value="1">
            </div>
        </div>
        <div class="form-group col-md-4 text-center">                                    
            <input type="text" class="form-control" name="txtConcepViat' . $c . '" value="Dia ' . $day . '" placeholder="Dia">
        </div>
        <div class="form-group col-md-3 text-center">                                    
            <input type="number" class="form-control" name="numValViat' . $c . '" value="' . $val . '" placeholder="Valor viático">
        </div>
        <div class="form-group col-md-3 text-center">                                    
            <input type="date" class="form-control" name="datFecViat' . $c . '" value="' . date("Y-m-d") . '">
        </div>';
} else {
    $res = '0';
}
echo $res;
