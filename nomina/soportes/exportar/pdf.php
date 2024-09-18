<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$idsop = isset($_POST['idsoporte']) ? $_POST['idsoporte'] : exit('Acción no permitida');
$vigencia = $_SESSION['vigencia'];
include '../../../conexion.php';
require('../../../fpdf/fpdf.php');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT shash FROM nom_soporte_ne
    WHERE id_soporte = '$idsop'";
    $rs = $cmd->query($sql);
    $soporte = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

if (!empty($soporte)) {
    echo 'https://api.taxxa.co/nominaGet.dhtml?hash=' . $soporte['shash'];
} else {
    echo 0;
}
