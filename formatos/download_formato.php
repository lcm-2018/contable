<?php
$archivo = $_POST['nom_file'];
if (!file_exists($archivo)) {
    echo "El fichero $archivo no existe";
    exit;
}
if (ob_get_length()) ob_end_clean();

header('Content-Disposition: attachment; filename="' . basename($archivo) . '"');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Length: ' . filesize($archivo));
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Envía el archivo
readfile($archivo);
exit;
