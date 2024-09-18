<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$idhe = isset($_POST['slcTipoHeup']) ? $_POST['slcTipoHeup'] : exit('Acción no permitida');
$finic = date('Y-m-d', strtotime($_POST['datFecLabHeIup']));
$ffin = date('Y-m-d', strtotime($_POST['datFecLabHeFup']));
$hinic = $_POST['timeInicioHeup'];
$hfin = $_POST['timeFinHeup'];
$cant = $_POST['numCantHe'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$idhelab = $_POST['idHelab'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE nom_horas_ex_trab SET id_he = ?, fec_inicio = ?, fec_fin = ?, hora_inicio = ?, hora_fin = ?, cantidad_he = ? WHERE id_he_trab = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idhe, PDO::PARAM_INT);
    $sql->bindParam(2, $finic, PDO::PARAM_STR);
    $sql->bindParam(3, $ffin, PDO::PARAM_STR);
    $sql->bindParam(4, $hinic, PDO::PARAM_STR);
    $sql->bindParam(5, $hfin, PDO::PARAM_STR);
    $sql->bindParam(6, $cant, PDO::PARAM_STR);
    $sql->bindParam(7, $idhelab, PDO::PARAM_INT);
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
        $sql = "UPDATE nom_horas_ex_trab SET fec_actu = ? WHERE id_he_trab = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(2, $idhelab);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo '1';
        } else {
        }
    } else {
        echo 'No se ingresó ningún dato nuevo.';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo  $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
