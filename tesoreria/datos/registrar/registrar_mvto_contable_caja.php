<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$acto = $_POST['slcTipActo'];
$num_acto = $_POST['numActo'];
$nom_caja = $_POST['txtNomCaja'];
$fec_inicia = $_POST['fecIniciaCaja'];
$fec_acto = $_POST['fecActoDc'];
$poliza = $_POST['txtPoliza'];
$val_total = $_POST['valTotal'];
$val_minimo = $_POST['valMinimo'];
$porcentajeCs = $_POST['porcentajeCs'];
$id = $_POST['id'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($id == 0) {
        $estado = 1;
        $query = "INSERT INTO `tes_caja_const`
                    (`id_tipo_acto`,`num_acto`,`nombre_caja`,`fecha_ini`,`fecha_acto`,`valor_total`,`valor_minimo`,`num_poliza`,`porcentaje`,`estado`,`id_user_reg`,`fec_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $acto, PDO::PARAM_INT);
        $query->bindParam(2, $num_acto, PDO::PARAM_STR);
        $query->bindParam(3, $nom_caja, PDO::PARAM_STR);
        $query->bindParam(4, $fec_inicia, PDO::PARAM_STR);
        $query->bindParam(5, $fec_acto, PDO::PARAM_STR);
        $query->bindParam(6, $val_total, PDO::PARAM_STR);
        $query->bindParam(7, $val_minimo, PDO::PARAM_STR);
        $query->bindParam(8, $poliza, PDO::PARAM_STR);
        $query->bindParam(9, $porcentajeCs, PDO::PARAM_STR);
        $query->bindParam(10, $estado, PDO::PARAM_INT);
        $query->bindParam(11, $iduser, PDO::PARAM_INT);
        $query->bindParam(12, $fecha2);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            echo 'ok';
        } else {
            echo $query->errorInfo()[2];
        }
    } else {
        $query = "UPDATE `tes_caja_const`
                    SET `id_tipo_acto` = ?, `num_acto` = ?, `nombre_caja` = ?, `fecha_ini` = ?, `fecha_acto` = ?, `valor_total` = ?, `valor_minimo` = ?, `num_poliza` = ?, `porcentaje` = ?
                    WHERE (`id_caja_const` = ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $acto, PDO::PARAM_INT);
        $query->bindParam(2, $num_acto, PDO::PARAM_STR);
        $query->bindParam(3, $nom_caja, PDO::PARAM_STR);
        $query->bindParam(4, $fec_inicia, PDO::PARAM_STR);
        $query->bindParam(5, $fec_acto, PDO::PARAM_STR);
        $query->bindParam(6, $val_total, PDO::PARAM_STR);
        $query->bindParam(7, $val_minimo, PDO::PARAM_STR);
        $query->bindParam(8, $poliza, PDO::PARAM_STR);
        $query->bindParam(9, $porcentajeCs, PDO::PARAM_STR);
        $query->bindParam(10, $id, PDO::PARAM_INT);
        if (!($query->execute())) {
            echo $query->errorInfo()[2] . $query->queryString;
        } else {
            if ($query->rowCount() > 0) {
                $query = "UPDATE `tes_caja_const` SET `id_user_act` = ?, `fecha_act` = ? WHERE (`id_caja_const` = ?)";
                $query = $cmd->prepare($query);
                $query->bindParam(1, $iduser, PDO::PARAM_INT);
                $query->bindParam(2, $fecha2, PDO::PARAM_STR);
                $query->bindParam(3, $id, PDO::PARAM_INT);
                $query->execute();
                echo 'ok';
            } else {
                echo 'No se realizó ningún cambio';
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getMessage();
}
