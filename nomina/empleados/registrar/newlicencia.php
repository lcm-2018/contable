<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$idemple = isset($_POST['idEmpLicencia']) ? $_POST['idEmpLicencia'] : exit('Acción no permitida');
$tiplc = $_POST['txtTipoLic'];
$inicio = date('Y-m-d', strtotime($_POST['datFecInicioLic']));
$fin = date('Y-m-d', strtotime($_POST['datFecFinLic']));
$dinac = $_POST['numCantDiasLic'];
$dhab = $_POST['numCantDiasHabLic'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO nom_licenciasmp (id_empleado, tipo, fec_inicio, fec_fin, dias_inactivo, dias_habiles, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idemple, PDO::PARAM_INT);
    $sql->bindParam(2, $tiplc, PDO::PARAM_STR);
    $sql->bindParam(3, $inicio, PDO::PARAM_STR);
    $sql->bindParam(4, $fin, PDO::PARAM_STR);
    $sql->bindParam(5, $dinac, PDO::PARAM_STR);
    $sql->bindParam(6, $dhab, PDO::PARAM_STR);
    $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
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
