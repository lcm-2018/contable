<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$id_cargue = isset($_POST['id_cargue']) ? $_POST['id_cargue'] : exit('Acceso no permitido');

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    // Valido que el rubro no tenga cuentas asociadas
    $sql = "SELECT `cod_pptal`, `id_pto` FROM `pto_cargue` WHERE `id_cargue` = $id_cargue";
    $rs = $cmd->query($sql);
    $codigo = $rs->fetch();
    $cod_pptal = $codigo['cod_pptal'];
    $id_pto = $codigo['id_pto'];
    // consulta codigo asociado
    $sql = "SELECT `cod_pptal` FROM `pto_cargue` WHERE `cod_pptal` LIKE '$cod_pptal%' AND `id_pto` = $id_pto";
    $rs = $cmd->query($sql);
    $fil = $rs->rowCount();
    //Pendiente ajustar consulta para poder eliminar 
    $sql = "SELECT `id_pto_mvto` FROM `pto_documento_detalles` WHERE `rubro` = 'cod_pptal'";
    $rs = $cmd->query($sql);
    $fil2 = $rs->rowCount();
    if ($fil > 1) {
        echo 'No se puede eliminar el registro, tiene cuentas asociadas';
    } else if ($fil2 > 1) {
        echo 'El rubro ya fue utilizado en movimientos presupuestales';
    } else {
        $sql = "DELETE FROM `pto_cargue`  WHERE `id_cargue` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $idpto, PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo 'ok';
        } else {
            echo $sql->errorInfo()[2];
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
