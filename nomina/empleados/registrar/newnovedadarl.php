<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$idemple = isset($_POST['idEmpNovArl']) ? $_POST['idEmpNovArl'] : exit('Acción no permitida');
$arl = $_POST['slcArlNovedad'];
$riesgo = $_POST['slcRiesLabNov'];
$afilarl = date('Y-m-d', strtotime($_POST['datFecAfilArlNovedad']));
if ($_POST['datFecRetArlNovedad'] === '') {
    $retarl;
} else {
    $retarl = date('Y-m-d', strtotime($_POST['datFecRetArlNovedad']));
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO nom_novedades_arl (id_empleado, id_arl, id_riesgo, fec_afiliacion, fec_retiro, fec_reg) VALUES (?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idemple, PDO::PARAM_INT);
    $sql->bindParam(2, $arl, PDO::PARAM_INT);
    $sql->bindParam(3, $riesgo, PDO::PARAM_INT);
    $sql->bindParam(4, $afilarl, PDO::PARAM_STR);
    $sql->bindParam(5, $retarl, PDO::PARAM_STR);
    $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo '1';
    } else {
        print_r($sql->errorInfo()[2]);
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
