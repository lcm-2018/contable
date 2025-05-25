<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_doc = isset($_POST['id_doc_ref']) ? $_POST['id_doc_ref'] : exit('Acceso no autorizado');
$id_ctb_ref = $_POST['id_ctb_ref'];
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';
$id_cuenta = isset($_POST['id_codigoCta1']) ? $_POST['id_codigoCta1'] : NULL;
$id_cta_credito = isset($_POST['id_codigoCta2']) ? $_POST['id_codigoCta2'] : NULL;
$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
$estado = 1;
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if (isset($_POST['eliminar'])) {
        $query = "DELETE FROM `ctb_referencia` WHERE `id_ctb_referencia` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_ctb_ref, PDO::PARAM_INT);
        if ($query->execute()) {
            echo 'ok';
        } else {
            echo $query->errorInfo()[2];
        }
        exit();
    }
    if ($id_ctb_ref == 0) {
        $query = "INSERT INTO `ctb_referencia`
                    (`id_ctb_fuente`,`id_cuenta`,`nombre`,`accion`,`estado`,`id_user_reg`,`fecha_reg`,`id_cta_credito`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_doc, PDO::PARAM_INT);
        $query->bindParam(2, $id_cuenta, PDO::PARAM_INT);
        $query->bindParam(3, $nombre, PDO::PARAM_STR);
        $query->bindParam(4, $accion, PDO::PARAM_INT);
        $query->bindParam(5, $estado, PDO::PARAM_INT);
        $query->bindParam(6, $iduser, PDO::PARAM_INT);
        $query->bindParam(7, $fecha2);
        $query->bindParam(8, $id_cta_credito);
        $query->execute();
        $id = $cmd->lastInsertId();
        if ($cmd->lastInsertId() > 0) {
            echo 'ok';
        } else {
            echo $query->errorInfo()[2];
        }
    } else {
        if (isset($_POST['estado'])) {
            $std = $_POST['estado'];
            $query = "UPDATE `ctb_referencia`
                        SET `estado` = ?
                    WHERE `id_ctb_referencia` = ?";
            $query = $cmd->prepare($query);
            $query->bindParam(1, $std, PDO::PARAM_INT);
            $query->bindParam(2, $id_ctb_ref, PDO::PARAM_INT);
        } else {
            $query = "UPDATE `ctb_referencia`
                        SET `id_cuenta` = ?, `nombre` = ?, `accion` = ?, `id_cta_credito` = ?
                    WHERE `id_ctb_referencia` = ?";
            $query = $cmd->prepare($query);
            $query->bindParam(1, $id_cuenta, PDO::PARAM_INT);
            $query->bindParam(2, $nombre, PDO::PARAM_STR);
            $query->bindParam(3, $accion, PDO::PARAM_INT);
            $query->bindParam(4, $id_cta_credito, PDO::PARAM_INT);
            $query->bindParam(5, $id_ctb_ref, PDO::PARAM_INT);
        }
        if (!($query->execute())) {
            echo $query->errorInfo()[2];
        } else {
            if ($query->rowCount() > 0) {
                $query = $query = "UPDATE `ctb_referencia` SET `id_user_act` = ?, `fecha_act` = ? WHERE `id_ctb_referencia` = ?";
                $query = $cmd->prepare($query);
                $query->bindParam(1, $iduser, PDO::PARAM_INT);
                $query->bindParam(2, $fecha2);
                $query->bindParam(3, $id_ctb_ref, PDO::PARAM_INT);
                $query->execute();
                echo 'ok';
            } else {
                echo 'No se realizó ningún cambio';
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
