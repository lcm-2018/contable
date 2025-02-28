<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$idpto = isset($_POST['id']) ? $_POST['id'] : exit('Acceso denegado');

// Comprobar si en la tabla pto_cargue hay registros con el id_pto_presupuestos
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT `cod_pptal` FROM `pto_cargue`  WHERE `id_pto` = $idpto";
    $rs = $cmd->query($sql);
    $res = $rs->rowCount();
    if ($res > 0) {
        echo 'El presupuesto tiene rubros cargados';
        exit();
    }
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "DELETE FROM `pto_presupuestos`  WHERE `id_pto` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $idpto, PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            include '../../../financiero/reg_logs.php';
            $ruta = '../../../log';
            $consulta = "DELETE FROM `pto_presupuestos` WHERE `id_pto` = $idpto";
            RegistraLogs($ruta, $consulta);
            echo 'ok';
        } else {
            echo $sql->errorInfo()[2];
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
