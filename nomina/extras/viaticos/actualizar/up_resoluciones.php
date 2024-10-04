<?php

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
$id_resolucion = isset($_POST['id_resolucion']) ? $_POST['id_resolucion'] : exit('Acción no permitida');
$fec_inicia = $_POST['fec_inicia'];
$fec_final = $_POST['fec_final'];
$tot_dias = $_POST['tot_dias'];
$dias_pernocta = $_POST['dias_pernocta'];
$objetivo = $_POST['objetivo'];
$destino = $_POST['destino'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `nom_resolucion_viaticos` SET `fec_inicia` = ?, `fec_final` = ?, `tot_dias` = ?, `dias_pernocta` = ?, `objetivo` = ?, `destino` = ? WHERE `id_resol_viat` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $fec_inicia, PDO::PARAM_STR);
    $sql->bindParam(2, $fec_final, PDO::PARAM_STR);
    $sql->bindParam(3, $tot_dias, PDO::PARAM_INT);
    $sql->bindParam(4, $dias_pernocta, PDO::PARAM_INT);
    $sql->bindParam(5, $objetivo, PDO::PARAM_STR);
    $sql->bindParam(6, $destino, PDO::PARAM_STR);
    $sql->bindParam(7, $id_resolucion, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `nom_resolucion_viaticos` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_resol_viat` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_user, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_resolucion, PDO::PARAM_STR);
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
