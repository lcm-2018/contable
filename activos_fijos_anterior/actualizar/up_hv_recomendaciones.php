<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$id_serie = isset($_POST['id_serie']) ? $_POST['id_serie'] : exit('Acción no permitida');
$recons = $_POST['recon'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_num_serial`
                SET `recomendaciones` = ?  WHERE `id_serial` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $recons, PDO::PARAM_STR);
    $sql->bindParam(2, $id_serie, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $conx = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $conx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $query = "UPDATE `seg_num_serial` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_serial` = ?";
            $query = $conx->prepare($query);
            $query->bindParam(1, $iduser, PDO::PARAM_INT);
            $query->bindValue(2, $date->format('Y-m-d H:i:s'));
            $query->bindParam(3, $id_serie, PDO::PARAM_INT);
            $query->execute();
            if ($query->rowCount() > 0) {
                echo '1';
            } else {
                echo $query->errorInfo()[2];
                exit();
            }
        } else {
            echo 'No se ha registrado ningún dato nuevo';
        }
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
