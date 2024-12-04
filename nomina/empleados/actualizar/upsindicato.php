<?php

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$idsind = isset($_POST['numidSindicato']) ? $_POST['numidSindicato'] : exit('Acción no permitida');
$idsindicato = $_POST['slcUpSindicato'];
$porcentaje = str_replace(',', '.', $_POST['txtUpPorcentajeSind']) / 100;
$fini = date('Y-m-d', strtotime($_POST['datUpFecInicioSind']));
if ($_POST['datUpFecFinSind'] === '') {
    $ffin;
} else {
    $ffin = date('Y-m-d', strtotime($_POST['datUpFecFinSind']));
}
$val_sind = $_POST['numValSindicalizar'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `nom_cuota_sindical` SET `id_sindicato` = ?, `porcentaje_cuota` = ?, `fec_inicio` = ?, `fec_fin` = ?, `val_sidicalizacion` = ?  WHERE `id_cuota_sindical` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idsindicato, PDO::PARAM_INT);
    $sql->bindParam(2, $porcentaje, PDO::PARAM_STR);
    $sql->bindParam(3, $fini, PDO::PARAM_STR);
    $sql->bindParam(4, $ffin, PDO::PARAM_STR);
    $sql->bindParam(5, $val_sind, PDO::PARAM_STR);
    $sql->bindParam(6, $idsind, PDO::PARAM_INT);
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
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "UPDATE nom_cuota_sindical SET  fec_act = ? WHERE id_cuota_sindical = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(2, $idsind, PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo '1';
        } else {
            echo $sql->errorInfo()[2];
        }
    } else {
        echo 'No se registró ningún dato nuevo.';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo  $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
