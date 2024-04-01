<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$idemple = isset($_POST['idEmpLicNR']) ? $_POST['idEmpLicNR'] : exit('Acción no permitida');
$inicio = date('Y-m-d', strtotime($_POST['datFecInicioLicNR']));
$fin = date('Y-m-d', strtotime($_POST['datFecFinLicNR']));
$dinac = $_POST['numCantDiasLicNR'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `nom_indemniza_vac` (`id_empleado`, `fec_inica`, `fec_fin`, `cant_dias`, `id_user_reg`, `fec_reg`) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idemple, PDO::PARAM_INT);
    $sql->bindParam(2, $inicio, PDO::PARAM_STR);
    $sql->bindParam(3, $fin, PDO::PARAM_STR);
    $sql->bindParam(4, $dinac, PDO::PARAM_STR);
    $sql->bindParam(5, $id_user, PDO::PARAM_INT);
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
