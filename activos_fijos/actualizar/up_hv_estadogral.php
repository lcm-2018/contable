<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$id_traslado = isset($_POST['id_traslado']) ? $_POST['id_traslado'] : exit('Acción no permitida');
$estado = isset($_POST['estadoGral']) ? $_POST['estadoGral'] : exit('Acción no permitida');
$causas = $_POST['txtaEstadoGral'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_ubica_traslado_centro_costo`
                    SET `estado` = ?, `mmto_causas` = ? WHERE `id_traslado` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $estado, PDO::PARAM_STR);
    $sql->bindParam(2, $causas, PDO::PARAM_STR);
    $sql->bindParam(3, $id_traslado, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $conx = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $conx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $query = "UPDATE `seg_ubica_traslado_centro_costo` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_traslado` = ?";
            $query = $conx->prepare($query);
            $query->bindParam(1, $iduser, PDO::PARAM_INT);
            $query->bindValue(2, $date->format('Y-m-d H:i:s'));
            $query->bindParam(3, $id_traslado, PDO::PARAM_INT);
            $query->execute();
            if ($query->rowCount() > 0) {
                echo '1';
            } else {
                echo $query->errorInfo()[2];
                exit();
            }
        } else {
            echo 'No se ha ingresado ningún dato nuevo';
        }
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
