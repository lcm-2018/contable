<?php
$archivo = "../registrar/form_hoex.xlsx";
if (!file_exists($archivo)) {
    echo "El fichero $archivo no existe";
    exit;
}
header('Content-Disposition: attachment;filename=formato_hoex.xlsx');
header('Content-Type: application/vnd.ms-excel');
header('Content-Length: ' . filesize($archivo));
header('Cache-Control: max-age=0');
readfile($archivo);
exit;
