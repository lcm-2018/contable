<?php
session_start();
$fini = new DateTime($_POST['i']);
$ffin = new DateTime($_POST['f']);
$diferencia = $fini->diff($ffin);
$dias = intval($diferencia->format('%d')) + 1;
$meses = intval($diferencia->format('%m')) > 0 ? intval($diferencia->format('%m')) . ' mes(es) ' : '';
echo $meses . $dias . ' día(s)';
