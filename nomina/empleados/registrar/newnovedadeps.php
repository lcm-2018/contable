<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$idemple = isset($_POST['idEmpNovEps']) ? $_POST['idEmpNovEps'] : exit('Acción no permitida');
$eps = $_POST['slcEpsNovedad'];
$afileps = date('Y-m-d', strtotime($_POST['datFecAfilEpsNovedad']));
if ($_POST['datFecRetEpsNovedad'] === '') {
    $reteps;
} else {
    $reteps = date('Y-m-d', strtotime($_POST['datFecRetEpsNovedad']));
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO nom_novedades_eps (id_empleado, id_eps, fec_afiliacion, fec_retiro, fec_reg) VALUES (?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idemple, PDO::PARAM_INT);
    $sql->bindParam(2, $eps, PDO::PARAM_INT);
    $sql->bindParam(3, $afileps, PDO::PARAM_STR);
    $sql->bindParam(4, $reteps, PDO::PARAM_STR);
    $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
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
