<?php

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$idemple = isset($_POST['idEmpVacacion']) ? $_POST['idEmpVacacion'] : exit('Acción no permitida');
$corte = $_POST['fecCorteVac'];
$diastocalc = $_POST['numDiasToCalc'];
$antic = $_POST['slcVacAnticip'];
$inicio = date('Y-m-d', strtotime($_POST['datFecInicioVac']));
$fin = date('Y-m-d', strtotime($_POST['datFecFinVac']));
$dinac = $_POST['numCantDiasVac'];
$dhab = $_POST['numCantDiasHabVac'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `nom_vacaciones` (`id_empleado`, `anticipo`, `fec_inicial`, `fec_inicio`, `fec_fin`, `dias_inactivo`, `dias_habiles`, `corte`, `dias_liquidar`, `fec_reg`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idemple, PDO::PARAM_INT);
    $sql->bindParam(2, $antic, PDO::PARAM_STR);
    $sql->bindParam(3, $inicio, PDO::PARAM_STR);
    $sql->bindParam(4, $inicio, PDO::PARAM_STR);
    $sql->bindParam(5, $fin, PDO::PARAM_STR);
    $sql->bindParam(6, $dinac, PDO::PARAM_STR);
    $sql->bindParam(7, $dhab, PDO::PARAM_STR);
    $sql->bindParam(8, $corte, PDO::PARAM_STR);
    $sql->bindParam(9, $diastocalc, PDO::PARAM_STR);
    $sql->bindValue(10, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo 'ok';
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
