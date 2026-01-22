<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$id_causacion = isset($_POST['id_causacion']) ? $_POST['id_causacion'] : exit("Acción no permitida");
$id_tipo = $_POST['slcTipo'];
$c_costo = $_POST['slcCentroCosto'];
$detalle = ($_POST['txtBuscaCuentaCtb'] == '') ? NULL : trim(explode('->', $_POST['txtBuscaCuentaCtb'])[1]);
$id_cuenta = $_POST['idCtaCtb'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$id_user = $_SESSION['id_user'];
if ($id_causacion == '0') {
    $condicion = '';
} else {
    $condicion = ' AND `id_causacion` <> ' . $id_causacion;
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_causacion` FROM `nom_causacion` WHERE `centro_costo` = $c_costo AND `id_tipo` = $id_tipo $condicion";
    $rs = $cmd->query($sql);
    $valida = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($valida)) {
    echo 'Ya se asignó una cuenta a este centro de costo';
    exit();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    if ($id_causacion == '0') {
        $sql = "INSERT INTO `nom_causacion`
                    (`centro_costo`,`id_tipo`,`cuenta`,`detalle`,`id_user_reg`,`fec_reg`)
                VALUES(?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $c_costo, PDO::PARAM_INT);
        $sql->bindParam(2, $id_tipo, PDO::PARAM_INT);
        $sql->bindParam(3, $id_cuenta, PDO::PARAM_INT);
        $sql->bindParam(4, $detalle, PDO::PARAM_STR);
        $sql->bindParam(5, $id_user, PDO::PARAM_INT);
        $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            echo 'ok';
        } else {
            echo $sql->errorInfo()[2];
            exit();
        }
    } else {
        $sql = "UPDATE `nom_causacion`
                    SET `centro_costo` = ?, `id_tipo` = ?, `cuenta` = ?, `detalle` = ?
                WHERE `id_causacion` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $c_costo, PDO::PARAM_STR);
        $sql->bindParam(2, $id_tipo, PDO::PARAM_INT);
        $sql->bindParam(3, $id_cuenta, PDO::PARAM_INT);
        $sql->bindParam(4, $detalle, PDO::PARAM_STR);
        $sql->bindParam(5, $id_causacion, PDO::PARAM_INT);
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $sql = $sql = "UPDATE `nom_causacion` SET `id_user_act` = ?, `fec_act` = ? WHERE `id_causacion` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id_user, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $id_causacion, PDO::PARAM_INT);
                $sql->execute();
                echo 'ok';
            } else {
                echo 'No se realizó ningún cambio';
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
