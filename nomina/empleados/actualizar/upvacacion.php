<?php

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$idvac = isset($_POST['numidVacacion']) ? $_POST['numidVacacion'] : exit('Acción no permitida');
$corte = $_POST['fecCorteVac'];
$dias_calc = $_POST['numDiasToCalc'];
$antic = $_POST['slcVacAnticip'];
$fini = date('Y-m-d', strtotime($_POST['datFecInicioVac']));
$ffin = date('Y-m-d', strtotime($_POST['datFecFinVac']));
$diainact = $_POST['numCantDiasVac'];
$diahabi = $_POST['numCantDiasHabVac'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `nom_vacaciones` SET `anticipo` = ?, `fec_inicial` = ?, `fec_inicio` = ?, `fec_fin` = ?,  `dias_inactivo` = ?, `dias_habiles` = ?, `corte` = ?, `dias_liquidar` = ? WHERE `id_vac` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $antic, PDO::PARAM_STR);
    $sql->bindParam(2, $fini, PDO::PARAM_STR);
    $sql->bindParam(3, $fini, PDO::PARAM_STR);
    $sql->bindParam(4, $ffin, PDO::PARAM_STR);
    $sql->bindParam(5, $diainact, PDO::PARAM_INT);
    $sql->bindParam(6, $diahabi, PDO::PARAM_INT);
    $sql->bindParam(7, $corte, PDO::PARAM_STR);
    $sql->bindParam(8, $dias_calc, PDO::PARAM_INT);
    $sql->bindParam(9, $idvac, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $updata = 1;
    } else {
        $updata = 0;
    }
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    }
    if ($updata > 0) {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE nom_vacaciones SET  fec_act = ? WHERE id_vac = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(2, $idvac, PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo 'ok';
        } else {
            echo $sql->errorInfo()[2];
        }
    } else {
        echo 'No se ingresó ningún dato nuevo.';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
