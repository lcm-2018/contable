<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$id_fno = $_POST['id'] ? $_POST['id'] : exit('Acción no permitida');
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$hoy = $date->format('Y-m-d');
$fecha = $date->format('Y-m-d H:i:s');
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `ctt_fact_noobligado`
                    (`id_tercero_no`, `fec_compra`, `fec_vence`, `met_pago`, `forma_pago`, `val_retefuente`
                    , `porc_retefuente`, `val_reteiva`, `porc_reteiva`, `val_iva`, `porc_iva`, `val_dcto`
                    , `porc_dcto`, `observaciones`, `vigencia`,`tipo_doc`, `id_user_reg`, `fec_reg`)
                SELECT
                    `id_tercero_no`, '$hoy' AS  `fec_compra`, `fec_vence`, `met_pago`, `forma_pago`, `val_retefuente`
                    , `porc_retefuente`, `val_reteiva`, `porc_reteiva`, `val_iva`, `porc_iva`, `val_dcto`
                    , `porc_dcto`, `observaciones`, `vigencia`, 1 AS `tipo_doc`, $iduser AS `id_user_reg`
                    , '$fecha' AS `fec_reg`
                FROM
                `ctt_fact_noobligado`
                WHERE (`id_facturano` = $id_fno)";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    $idF = $cmd->lastInsertId();
    if ($idF > 0) {
        $cmd = null;
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `ctt_fact_noobligado_det`
                    (`id_fno`,`codigo`,`detalle`,`val_unitario`,`cantidad`,
                    `p_iva`,`val_iva`,`p_dcto`,`val_dcto`,`id_user_reg`,`fec_reg`)
                SELECT
                    $idF AS `id_fno`,`codigo`,`detalle`,`val_unitario`,`cantidad`,
                    `p_iva`,`val_iva`,`p_dcto`,`val_dcto`,$iduser AS `id_user_reg`,'$fecha' AS `fec_reg`
                FROM  `ctt_fact_noobligado_det`
                WHERE (`id_fno` = $id_fno)";
        $sql = $cmd->prepare($sql);
        $sql->execute();
        $ids = $cmd->lastInsertId();
        if ($ids > 0) {
            $cmd = null;
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `ctt_fact_noobligado`
                    SET `id_doc_anula` = ?
                    WHERE `id_facturano` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $idF);
            $sql->bindParam(2, $id_fno);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo 'ok';
            } else {
                echo $cmd->errorInfo()[2];
            }
        } else {
            echo $cmd->errorInfo()[2];
        }
    } else {
        echo $cmd->errorInfo()[2];
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
