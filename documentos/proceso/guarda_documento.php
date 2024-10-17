<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php");
    exit();
}
include '../../conexion.php';
$id_maestro = isset($_POST['id_maestro']) ? $_POST['id_maestro'] : exit('Acción no permitida');
$id_modulo = $_POST['id_modulo'];
$id_doc_fte = $_POST['id_doc_fte'];
$version = $_POST['version_doc'];
$control_doc = $_POST['control'];
$fec_doc = $_POST['fecha_doc'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($id_maestro == 0) {
        $sql = "SELECT `id_maestro` FROM `fin_maestro_doc` WHERE `id_modulo` = ? AND `id_doc_fte` = ? AND `estado` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_modulo, PDO::PARAM_INT);
        $sql->bindParam(2, $id_doc_fte, PDO::PARAM_INT);
        $sql->bindValue(3, 1, PDO::PARAM_INT);
        $sql->execute();
        $documento = $sql->fetch(PDO::FETCH_ASSOC);
        if (empty($documento)) {
            $sql = "INSERT INTO `fin_maestro_doc`
	                (`id_modulo`,`id_doc_fte`,`version_doc`,`fecha_doc`,`estado`,`control_doc`,`id_user_reg`,`fecha_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_modulo, PDO::PARAM_INT);
            $sql->bindParam(2, $id_doc_fte, PDO::PARAM_INT);
            $sql->bindParam(3, $version, PDO::PARAM_STR);
            $sql->bindParam(4, $fec_doc, PDO::PARAM_STR);
            $sql->bindValue(5, 1, PDO::PARAM_INT);
            $sql->bindParam(6, $control_doc, PDO::PARAM_INT);
            $sql->bindParam(7, $iduser, PDO::PARAM_INT);
            $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
            $sql->execute();
            if ($cmd->lastInsertId() > 0) {
                echo 'ok';
            } else {
                echo $sql->errorInfo()[2];
            }
        } else {
            echo 'El documento ya existe debe desactivar el documento actual para poder crear uno nuevo';
        }
    } else {
        $sql = "UPDATE `fin_maestro_doc`
                    SET `id_modulo` = ?, `id_doc_fte` = ?, `version_doc` = ?, `fecha_doc` = ?, `control_doc` = ?
                WHERE `id_maestro` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_modulo, PDO::PARAM_INT);
        $sql->bindParam(2, $id_doc_fte, PDO::PARAM_INT);
        $sql->bindParam(3, $version, PDO::PARAM_STR);
        $sql->bindParam(4, $fec_doc, PDO::PARAM_STR);
        $sql->bindParam(5, $control_doc, PDO::PARAM_INT);
        $sql->bindParam(6, $id_maestro, PDO::PARAM_INT);
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
        } else {
            if ($sql->rowCount() > 0) {
                $sql = "UPDATE `fin_maestro_doc` SET `id_user_act` = ?, `fecha_act` = ? WHERE `id_maestro` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $id_maestro, PDO::PARAM_INT);
                $sql->execute();
                if ($sql->rowCount() > 0) {
                    echo 'ok';
                } else {
                    echo $sql->errorInfo()[2];
                }
            } else {
                echo 'No se realizaron cambios';
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
