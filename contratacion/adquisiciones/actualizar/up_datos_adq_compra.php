<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_compra = isset($_POST['idAdqCompra']) ? $_POST['idAdqCompra'] : exit('Acción no permitida');
$fec_orden = $_POST['datUpFecAdqCompra'];
$mod_cont = $_POST['slcModalidad'];
$id_pretbnsv = $_POST['tpBnSv'];
$id_posttbnsv = $_POST['slcTipoBnSv'];
$valor_c = $_POST['numTotalContrato'];
$objeto = mb_strtoupper($_POST['txtObjeto']);
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
if ($id_pretbnsv === $id_posttbnsv) {
    upCompra();
} else {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "DELETE FROM ctt_adquisicion_detalles  WHERE id_adquisicion = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_compra, PDO::PARAM_INT);
        $sql->execute();
        if (!($sql->rowCount() > 0)) {
            echo $sql->errorInfo()[2];
        }
        upCompra();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

function upCompra()
{
    try {
        include '../../../conexion.php';
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE `ctt_adquisiciones` SET `id_modalidad` = ?, `fecha_adquisicion` = ?, `val_contrato` = ?, `id_tipo_bn_sv` = ?, `objeto` = ? WHERE `id_adquisicion` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $GLOBALS['mod_cont'], PDO::PARAM_INT);
        $sql->bindParam(2, $GLOBALS['fec_orden'], PDO::PARAM_STR);
        $sql->bindParam(3, $GLOBALS['valor_c'], PDO::PARAM_STR);
        $sql->bindParam(4, $GLOBALS['id_posttbnsv'], PDO::PARAM_INT);
        $sql->bindParam(5, $GLOBALS['objeto'], PDO::PARAM_STR);
        $sql->bindParam(6, $GLOBALS['id_compra'], PDO::PARAM_INT);
        $sql->execute();
        $cambio = $sql->rowCount();
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
        } else {
            if ($cambio > 0) {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "UPDATE ctt_adquisiciones SET  id_user_act = ? ,fec_act = ? WHERE id_adquisicion = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $GLOBALS['iduser'], PDO::PARAM_INT);
                $sql->bindValue(2, $GLOBALS['date']->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $GLOBALS['id_compra'], PDO::PARAM_INT);
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
}
