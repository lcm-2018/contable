<?php
$archivo = "formato_planilla.xlsx";
if (!file_exists($archivo)) {
    echo "El fichero $archivo no existe";
    exit;
}
header('Content-Disposition: attachment;filename=formato_planilla.xlsx');
header('Content-Type: application/vnd.ms-excel');
header('Content-Length: ' . filesize($archivo));
header('Cache-Control: max-age=0');
readfile($archivo);
exit;
