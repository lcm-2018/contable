<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
$ruta = isset($_POST['ruta']) ? $_POST['ruta'] : exit('Acción no permitida');
$datas = explode('/', base64_decode($ruta));
$nom_file = end($datas);
$datos_nom = explode('_', $nom_file);
$anio = substr($datos_nom[1], 0, 4);
$cc = $datos_nom[2];
$nombre = 'form220_' . $anio . '_' . $cc . '.pdf';
//API URL
include '../../../../conexion.php';
$url = $api . 'terceros/datos/res/descargar/docs/soporte/' . $ruta;
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$result = curl_exec($ch);
curl_close($ch);
$res = json_decode($result, true);
if ($res == 0) {
    echo 'Archivo no disponible';
    exit();
} else {
    $filedocx = base64_decode($res['file']);
    $filebase = 'certificado.docx';
    file_put_contents($filebase, $filedocx);
    $filepdf = 'certificado.pdf';
    $tempLibreOfficeProfile = sys_get_temp_dir() . "\\LibreOfficeProfile" . rand(100000, 999999);
    $convertir = '"C:\Program Files\LibreOffice\program\soffice.exe" "-env:UserInstallation=file:///' . str_replace("\\", "/", $tempLibreOfficeProfile) . '" --headless --convert-to pdf "' . $filebase . '" --outdir "' . str_replace("\\", "/", dirname($filepdf)) . '"';
    exec($convertir);
    header("Content-Disposition: attachment; Filename=" . $nombre);
    echo file_get_contents($filepdf);
    unlink($filepdf);
    unlink($filebase);
}
