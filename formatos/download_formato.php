<?php
$archivo = $_POST['nom_file'];
if (!file_exists($archivo)) {
    echo "El fichero $archivo no existe";
    exit;
}
header('Content-Disposition: attachment;filename=' . $archivo);
header('Content-Type: application/vnd.ms-excel');
header('Content-Length: ' . filesize($archivo));
header('Cache-Control: max-age=0');
readfile($archivo);
exit;
