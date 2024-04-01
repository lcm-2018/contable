<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}

include '../../../../conexion.php';

$incremento = isset($_POST['numPorcentajeRetro']) ? $_POST['numPorcentajeRetro'] : exit('Acción no permitida');
$fecIni = $_POST['fecIniciaRetroactivo'];
$fecFin = $_POST['fecTerminaRetroactivo'];
$meses = $_POST['numMesesRetroactivo'];
$observaciones = $_POST['txtaObservaRetroActivo'];
$vigencia = $_SESSION['vigencia'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `nom_retroactivos` (`fec_inicio`, `fec_final`, `meses`, `id_incremento`, `observaciones`, `vigencia`, `id_user_reg`, `fec_reg`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $fecIni, PDO::PARAM_STR);
    $sql->bindParam(2, $fecFin, PDO::PARAM_STR);
    $sql->bindParam(3, $meses, PDO::PARAM_INT);
    $sql->bindParam(4, $incremento, PDO::PARAM_STR);
    $sql->bindParam(5, $observaciones, PDO::PARAM_STR);
    $sql->bindParam(6, $vigencia, PDO::PARAM_STR);
    $sql->bindParam(7, $iduser, PDO::PARAM_INT);
    $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo 1;
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
