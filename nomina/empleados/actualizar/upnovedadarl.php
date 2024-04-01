<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';

$idnovarl = isset($_POST['numidnovarl']) ? $_POST['numidnovarl'] : exit('Acción no permitida');
$idarl = $_POST['slcUpNovArl'];
$ries = $_POST['slcRiesLabNovup'];
$fafil = date('Y-m-d', strtotime($_POST['datFecAfilUpNovArl']));
if ($_POST['datFecRetUpNovArl'] === '') {
    $fret;
} else {
    $fret = date('Y-m-d', strtotime($_POST['datFecRetUpNovArl']));
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE nom_novedades_arl SET id_arl = ?, id_riesgo = ?, fec_afiliacion= ?, fec_retiro = ? WHERE id_novarl = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idarl, PDO::PARAM_INT);
    $sql->bindParam(2, $ries, PDO::PARAM_INT);
    $sql->bindParam(3, $fafil, PDO::PARAM_STR);
    $sql->bindParam(4, $fret, PDO::PARAM_STR);
    $sql->bindParam(5, $idnovarl, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $updata = 1;
    } else {
        $updata = 0;
    }
    if (!($sql->execute())) {
        print_r($sql->errorInfo()[2]);
        exit();
    }
    if ($updata > 0) {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE nom_novedades_arl SET  fec_act = ? WHERE id_novarl = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(2, $idnovarl, PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo '1';
        } else {
            print_r($sql->errorInfo()[2]);
        }
    } else {
        echo 'No se ingresó ningún dato nuevo';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
