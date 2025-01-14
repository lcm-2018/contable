<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../financiero/consultas.php';
$op = isset($_POST['opcion']) ? $_POST['opcion'] : exit('Acceso no disponible');
$id_rubroCod = $_POST['id_rubroCod'];
$valorDeb = str_replace(",", "", $_POST['valorDeb']);
$valorCred = str_replace(",", "", $_POST['valorCred']);
$id_pto_mod = $_POST['id_pto_mod'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$vigencia = $_SESSION['vigencia'];
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
try {
    $sql = "SELECT 
                `pto_cargue`.`cod_pptal`
                , `pto_cargue`.`nom_rubro`
                , `pto_cargue`.`valor_aprobado`
                , `pto_cargue`.`id_pto` 
                , `pto_presupuestos`.`id_tipo`
            FROM `pto_cargue`
                INNER JOIN `pto_presupuestos` 
                    ON (`pto_cargue`.`id_pto` = `pto_presupuestos`.`id_pto`)
            WHERE `id_cargue` = $id_rubroCod";
    $res = $cmd->query($sql);
    $row = $res->fetch();
    $total = !empty($row) ? $row['valor_aprobado'] : 0;
    $tipo_pto = $row['id_tipo'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $sql = "SELECT  `id_tipo_mod` FROM `pto_mod` WHERE (`id_pto_mod` = $id_pto_mod)";
    $res = $cmd->query($sql);
    $movto = $res->fetch();
    $tipo_mvto = $movto['id_tipo_mod'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($tipo_pto == 2 && ($tipo_mvto == 2 || $tipo_mvto == 3)) {
    $valorDeb = $valorCred;
    $valorCred = 0;
}
/*
if ($total == 0) {
    exit('El rubro no tiene valor aprobado');
}
$saldo = saldoRubroGastos($vigencia, $id_rubroCod, $cmd);
foreach ($saldo as $s) {
    $valor = $s['debito'] - $s['credito'];
    $total -= $valor;
}
if ($total < 0) {
    exit('El rubro no tiene valor disponible');
}*/
try {
    if ($op == 0) {
        $sql = "INSERT INTO `pto_mod_detalle`
                    (`id_pto_mod`,`id_cargue`,`valor_deb`,`valor_cred`,`id_user_reg`,`fecha_reg`)
                VALUES (?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_pto_mod, PDO::PARAM_INT);
        $sql->bindParam(2, $id_rubroCod, PDO::PARAM_INT);
        $sql->bindParam(3, $valorDeb, PDO::PARAM_STR);
        $sql->bindParam(4, $valorCred, PDO::PARAM_STR);
        $sql->bindParam(5, $iduser, PDO::PARAM_INT);
        $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            echo "ok";
        } else {
            echo $sql->errorInfo()[2];
        }
    } else {
        $sql = "UPDATE `pto_mod_detalle`
                    SET `id_cargue` = ?, `valor_deb` = ?, `valor_cred` = ?
                WHERE `id_pto_mod_det` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_rubroCod, PDO::PARAM_INT);
        $sql->bindParam(2, $valorDeb, PDO::PARAM_STR);
        $sql->bindParam(3, $valorCred, PDO::PARAM_STR);
        $sql->bindParam(4, $op, PDO::PARAM_INT);
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $sql = "UPDATE `pto_mod_detalle`
                            SET `id_user_act` = ?, `fec_act` = ?
                        WHERE `id_pto_mod_det` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $op, PDO::PARAM_INT);
                $sql->execute();
                echo 'ok';
            } else {
                echo 'No se registró ningún nuevo dato';
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
