<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_adq = isset($_POST['idAdq']) ? $_POST['idAdq'] : exit('Accion no permitida');
$aprobados = $_POST['check'];
$cantidades = $_POST['bnsv'];
$val_unitarios = $_POST['val_bnsv'];
$estado = 1;
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$c = 0;
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
try {
    $sql = "DELETE FROM `ctt_orden_compra` WHERE `id_adq` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_adq, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    }
    $sql = "INSERT INTO `ctt_orden_compra`
                (`id_adq`,`estado`,`id_user_reg`,`fec_reg`)
            VALUES (?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_adq, PDO::PARAM_INT);
    $sql->bindParam(2, $estado, PDO::PARAM_INT);
    $sql->bindParam(3, $iduser, PDO::PARAM_INT);
    $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    $id_orden = $cmd->lastInsertId();
    if ($id_orden > 0) {
        $sql = "INSERT INTO `ctt_orden_compra_detalle`
                    (`id_oc`,`id_servicio`,`cantidad`,`val_unid`,`id_user_reg`,`fec_reg`)
                VALUES (?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_orden, PDO::PARAM_INT);
        $sql->bindParam(2, $id_bnsv, PDO::PARAM_INT);
        $sql->bindParam(3, $cantidad, PDO::PARAM_INT);
        $sql->bindParam(4, $val_unitario, PDO::PARAM_STR);
        $sql->bindParam(5, $iduser, PDO::PARAM_INT);
        $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
        foreach ($aprobados as $key => $value) {
            $id_bnsv = $key;
            $cantidad = $cantidades[$key];
            $val_unitario = $val_unitarios[$key];
            $sql->execute();
            if ($cmd->lastInsertId() > 0) {
                $c++;
            } else {
                echo $sql->errorInfo()[2];
                exit();
            }
        }
    }
    if ($c > 0) {
        echo 'ok';
    } else {
        echo 'Error al guardar la orden de compra';
    }
    $cmd = NULL;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
