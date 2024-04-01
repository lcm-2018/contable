<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$idadq = isset($_POST['idAdq']) ? $_POST['idAdq'] : exit('Acción no permitida');
$cant = 0;
if (isset($_POST['check'])) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "DELETE FROM ctt_adquisicion_detalles  WHERE id_adquisicion = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $idadq, PDO::PARAM_INT);
        $sql->execute();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    $tBnSv = $_REQUEST['check'];
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    foreach ($tBnSv as $tBS) {
        $idBS = $tBS;
        $cantidad = $_POST['bnsv_' . $idBS];
        $valEs = $_POST['val_bnsv_' . $idBS];
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO ctt_adquisicion_detalles (id_adquisicion, id_bn_sv, cantidad, val_estimado_unid, id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $idadq, PDO::PARAM_INT);
            $sql->bindParam(2, $idBS, PDO::PARAM_INT);
            $sql->bindParam(3, $cantidad, PDO::PARAM_INT);
            $sql->bindParam(4, $valEs, PDO::PARAM_STR);
            $sql->bindParam(5, $iduser, PDO::PARAM_INT);
            $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
            $sql->execute();
            if ($cmd->lastInsertId() > 0) {
                $cant++;
            } else {
                print_r($sql->errorInfo()[2]);
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    }
    if ($cant > 0) {
        $estado = '2';
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE ctt_adquisiciones SET estado = ?, id_user_act = ?, fec_act = ? WHERE id_adquisicion = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $estado, PDO::PARAM_INT);
            $sql->bindParam(2, $iduser, PDO::PARAM_INT);
            $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(4, $idadq, PDO::PARAM_INT);
            $sql->execute();
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    }
    echo $cant;
} else {
    echo 'No se seleccionó ningun bien o servicio';
}
