<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}

include '../../../../conexion.php';

$id_retroactivo = isset($_POST['id_retroactivo']) ? $_POST['id_retroactivo'] : exit('Acción no permitida');
$fecIni = $_POST['fecIniciaRetroactivo'];
$fecFin = $_POST['fecTerminaRetroactivo'];
$meses = $_POST['numMesesRetroactivo'];
$incremento = $_POST['numPorcentajeRetro'];
$observaciones = $_POST['txtaObservaRetroActivo'];
$vigencia = $_SESSION['vigencia'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `nom_retroactivos`  SET `fec_inicio` = ?, `fec_final` = ?, `meses` = ?, `id_incremento` = ?, `observaciones` = ? WHERE `id_retroactivo` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $fecIni, PDO::PARAM_STR);
    $sql->bindParam(2, $fecFin, PDO::PARAM_STR);
    $sql->bindParam(3, $meses, PDO::PARAM_INT);
    $sql->bindParam(4, $incremento, PDO::PARAM_STR);
    $sql->bindParam(5, $observaciones, PDO::PARAM_STR);
    $sql->bindParam(6, $id_retroactivo, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `nom_retroactivos` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_retroactivo` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_retroactivo, PDO::PARAM_STR);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo '1';
            } else {
                echo $sql->errorInfo()[2];
            }
        } else {
            echo 'No se registró ningún nuevo dato';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
