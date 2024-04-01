<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$idlic = isset($_POST['numidLicencia']) ? $_POST['numidLicencia'] : exit('Acción no permitida');
$fini = date('Y-m-d', strtotime($_POST['datUpFecInicioLic']));
$ffin = date('Y-m-d', strtotime($_POST['datUpFecFinLic']));
$diainact = $_POST['numUpCantDiasLic'];
$diahabi = $_POST['numUpCantDiasHabLic'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE nom_licenciasmp SET fec_inicio = ?, fec_fin = ?,  dias_inactivo = ?, dias_habiles = ? WHERE id_licmp = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $fini, PDO::PARAM_STR);
    $sql->bindParam(2, $ffin, PDO::PARAM_STR);
    $sql->bindParam(3, $diainact, PDO::PARAM_INT);
    $sql->bindParam(4, $diahabi, PDO::PARAM_INT);
    $sql->bindParam(5, $idlic, PDO::PARAM_INT);
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
        $sql = "UPDATE nom_licenciasmp SET  fec_act = ? WHERE id_licmp = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(2, $idlic, PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo '1';
        } else {
            print_r($sql->errorInfo()[2]);
        }
    } else {
        echo 'No se ingresó ningún dato nuevo.';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}