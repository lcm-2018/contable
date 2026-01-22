<?php

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$idemple = isset($_POST['idEmp']) ? $_POST['idEmp'] : exit('Acción no permitida');
$valor = $_POST['valIntViv'];
$id_vivienda = $_POST['idIntViv'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$estado = 1;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($id_vivienda  == '0') {
        $sql = "INSERT INTO `nom_intereses_vivienda`
                (`id_empleado`,`valor`,`id_user_reg`,`fec_reg`)
            VALUES (?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $idemple, PDO::PARAM_INT);
        $sql->bindParam(2, $valor, PDO::PARAM_STR);
        $sql->bindParam(3, $iduser, PDO::PARAM_INT);
        $sql->bindValue(4, $date->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            echo 'ok';
        } else {
            echo $sql->errorInfo()[2];
        }
    } else {
        $sql = "UPDATE `nom_intereses_vivienda`
                    SET `valor` = ?
                WHERE `id_intv` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $valor, PDO::PARAM_STR);
        $sql->bindParam(2, $id_vivienda, PDO::PARAM_INT);
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $sql = "UPDATE `nom_intereses_vivienda`
                        SET `fec_act` = ?,
                            `id_user_act` = ?
                        WHERE `id_intv` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindValue(1, $date->format('Y-m-d H:i:s'), PDO::PARAM_STR);
                $sql->bindParam(2, $iduser, PDO::PARAM_INT);
                $sql->bindParam(3, $id_vivienda, PDO::PARAM_INT);
                $sql->execute();
                if ($sql->rowCount() > 0) {
                    echo 'ok';
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
