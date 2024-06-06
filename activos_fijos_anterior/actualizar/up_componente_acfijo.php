<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_comp_actfijo = isset($_POST['id_comp_actfijo']) ? $_POST['id_comp_actfijo'] : exit('Acción no permitida');
$idProd = $_POST['id_acfijo'];
$mantenimiento = $_POST['mantenimiento'];
$depresiacion = $_POST['slcDepresiacion'];
$marca = $_POST['txtMarca'];
$modelo = $_POST['txtModelo'];
$valunit = $_POST['numValUnita'];
$observacion = $_POST['txtObservaActFijo'];
$tipoActivo = $_POST['slcTipoActivo'];
$series = $_REQUEST['serieUp'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$cambio = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_entra_detalle_activos_fijos` SET `id_prod` = ?, `mantenimiento` = ?, `depreciable` = ?, `marca` = ?, `modelo` = ?, `val_unit` = ?, `descripcion` = ?, `id_tipo_activo` = ? WHERE `id_acfijo` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idProd, PDO::PARAM_INT);
    $sql->bindParam(2, $mantenimiento, PDO::PARAM_STR);
    $sql->bindParam(3, $depresiacion, PDO::PARAM_STR);
    $sql->bindParam(4, $marca, PDO::PARAM_STR);
    $sql->bindParam(5, $modelo, PDO::PARAM_STR);
    $sql->bindParam(6, $valunit, PDO::PARAM_STR);
    $sql->bindParam(7, $observacion, PDO::PARAM_STR);
    $sql->bindParam(8, $tipoActivo, PDO::PARAM_INT);
    $sql->bindParam(9, $id_comp_actfijo, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `seg_entra_detalle_activos_fijos` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_acfijo` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_comp_actfijo, PDO::PARAM_INT);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                $cambio = 1;
            } else {
                echo $sql->errorInfo()[2];
                exit();
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_num_serial` SET  `num_serial` = ? WHERE `id_serial` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $valor, PDO::PARAM_STR);
    $sql->bindParam(2, $id, PDO::PARAM_INT);
    foreach ($series as $key => $value) {
        $valor = $value;
        $id = $key;
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "UPDATE `seg_num_serial` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_serial` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $key, PDO::PARAM_INT);
                $sql->execute();
                if ($sql->rowCount() > 0) {
                    $cambio = 1;
                } else {
                    echo $sql->errorInfo()[2];
                    exit();
                }
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($cambio == 0) {
    echo ' No se ingresó ningún dato nuevo';
}
if ($cambio == 1) {
    echo '1';
}
