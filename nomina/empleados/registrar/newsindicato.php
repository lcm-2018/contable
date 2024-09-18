<?php

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$idemple = isset($_POST['idEmpSindicato']) ? $_POST['idEmpSindicato'] : exit('Acción no permitida');
$sindicato = $_POST['slcSindicato'];
$porcentaje = str_replace(',', '.', $_POST['txtPorcentajeSind']) / 100;
$finsind = date('Y-m-d', strtotime($_POST['datFecInicioSind']));
if ($_POST['datFecFinSind'] === '') {
    $ffinsind;
} else {
    $ffinsind = date('Y-m-d', strtotime($_POST['datFecFinSind']));
}
$valor = $_POST['numValSindicalizar'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `nom_cuota_sindical` (`id_sindicato`, `id_empleado`, `porcentaje_cuota`, `fec_inicio`, `fec_fin`, `val_sidicalizacion`, `fec_reg`) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $sindicato, PDO::PARAM_INT);
    $sql->bindParam(2, $idemple, PDO::PARAM_INT);
    $sql->bindParam(3, $porcentaje, PDO::PARAM_STR);
    $sql->bindParam(4, $finsind, PDO::PARAM_STR);
    $sql->bindParam(5, $ffinsind, PDO::PARAM_STR);
    $sql->bindParam(6, $valor, PDO::PARAM_STR);
    $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo '1';
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
