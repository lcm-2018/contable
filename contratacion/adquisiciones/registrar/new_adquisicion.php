<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$modalidad = isset($_POST['slcModalidad']) ? $_POST['slcModalidad'] : exit('Acción no permitida');
$id_Adq = $_POST['id_adquisicion'];
$filtro = $_POST['filtro'];
$id_empresa = '1';
$id_sede = '1';
$fec_adq = $_POST['datFecAdq'];
$val_cont = $_POST['numTotalContrato'];
$vig = $_POST['datFecVigencia'];
$area = $_POST['slcAreaSolicita'];
$tbnsv = $_POST['slcTipoBnSv'];
$obligaciones = '';
$id_tercero = $_POST['id_tercero'];
$objeto = mb_strtoupper($_POST['txtObjeto']);
$estado = '1';
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_tercero` FROM `seg_terceros` WHERE `id_tercero_api` = ? LIMIT 1";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_tercero, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        $id_tercero = $row['id_tercero'];
    } else {
        echo 'Tercero no relacionado en base propia';
        exit();
    }
    if ($id_Adq == 0) {
        $sql = "INSERT INTO `ctt_adquisiciones` (`id_modalidad`, `id_empresa`, `id_sede`, `id_area`, `fecha_adquisicion`, `val_contrato`, `vigencia`, `id_tipo_bn_sv`, `obligaciones`, `objeto`, `estado`, `id_user_reg`, `fec_reg`, `id_tercero`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $modalidad, PDO::PARAM_INT);
        $sql->bindParam(2, $id_empresa, PDO::PARAM_INT);
        $sql->bindParam(3, $id_sede, PDO::PARAM_INT);
        $sql->bindParam(4, $area, PDO::PARAM_INT);
        $sql->bindParam(5, $fec_adq, PDO::PARAM_STR);
        $sql->bindParam(6, $val_cont, PDO::PARAM_STR);
        $sql->bindParam(7, $vig, PDO::PARAM_STR);
        $sql->bindParam(8, $tbnsv, PDO::PARAM_INT);
        $sql->bindParam(9, $obligaciones, PDO::PARAM_STR);
        $sql->bindParam(10, $objeto, PDO::PARAM_STR);
        $sql->bindParam(11, $estado, PDO::PARAM_STR);
        $sql->bindParam(12, $iduser, PDO::PARAM_INT);
        $sql->bindValue(13, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(14, $id_tercero, PDO::PARAM_INT);
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            echo '1';
        } else {
            echo $sql->errorInfo()[2];
        }
    } else {
        $query = "UPDATE `ctt_adquisiciones` 
                    SET `id_modalidad` = ?, `id_area` = ?, `fecha_adquisicion` = ?, `val_contrato` = ?, `id_tipo_bn_sv` = ?, `objeto` = ?, `id_tercero` = ?
                WHERE `id_adquisicion` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $modalidad, PDO::PARAM_INT);
        $query->bindParam(2, $area, PDO::PARAM_INT);
        $query->bindParam(3, $fec_adq, PDO::PARAM_STR);
        $query->bindParam(4, $val_cont, PDO::PARAM_STR);
        $query->bindParam(5, $tbnsv, PDO::PARAM_INT);
        $query->bindParam(6, $objeto, PDO::PARAM_STR);
        $query->bindParam(7, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(8, $id_Adq, PDO::PARAM_INT);
        if (!($query->execute())) {
            echo $query->errorInfo()[2];
            exit();
        } else {
            if ($query->rowCount() > 0) {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "UPDATE `ctt_adquisiciones` SET  `id_user_act` = ?, `fec_act` = ? WHERE `id_adquisicion` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $id_Adq, PDO::PARAM_INT);
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
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
