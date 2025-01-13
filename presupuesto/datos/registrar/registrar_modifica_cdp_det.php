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
$id_pto_cdp = $_POST['id_cdp'];
$valorDeb = str_replace(",", "", $_POST['valorDeb']);
$valorCred = 0;
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$vigencia = $_SESSION['vigencia'];
$response['status'] = 'error';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
try {
    $sql = "SELECT `cod_pptal`, `nom_rubro`, `valor_aprobado`, `id_pto` 
            FROM `pto_cargue` 
            WHERE `id_cargue` = $id_rubroCod";
    $res = $cmd->query($sql);
    $row = $res->fetch();
    $total = !empty($row) ? $row['valor_aprobado'] : 0;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
/*
if ($total == 0) {
    $response['msg'] = 'El rubro no tiene valor aprobado';
    echo json_encode($response);
    exit();
}
$saldo = saldoRubroGastos($vigencia, $id_rubroCod, $cmd);
foreach ($saldo as $s) {
    $valor = $s['debito'] - $s['credito'];
    $total -= $valor;
}
if ($total < 0) {
    $response['msg'] = 'El rubro no tiene valor disponible';
    echo json_encode($response);
    exit();
 }*/
try {
    if ($op == 0) {
        $sql = "INSERT INTO `pto_cdp_detalle`
                    (`id_pto_cdp`,`id_rubro`,`valor`,`valor_liberado`,`id_user_reg`,`fecha_reg`)
                VALUES (?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_pto_cdp, PDO::PARAM_INT);
        $sql->bindParam(2, $id_rubroCod, PDO::PARAM_INT);
        $sql->bindParam(3, $valorDeb, PDO::PARAM_STR);
        $sql->bindParam(4, $valorCred, PDO::PARAM_STR);
        $sql->bindParam(5, $iduser, PDO::PARAM_INT);
        $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $response['status'] = "ok";
        } else {
            $response['msg'] = $sql->errorInfo()[2];
        }
    } else {
        $sql = "UPDATE `pto_cdp_detalle`
                    SET `id_rubro` = ?, `valor` = ?, `valor_liberado` = ?
                WHERE `id_pto_cdp_det` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_rubroCod, PDO::PARAM_INT);
        $sql->bindParam(2, $valorDeb, PDO::PARAM_STR);
        $sql->bindParam(3, $valorCred, PDO::PARAM_STR);
        $sql->bindParam(4, $op, PDO::PARAM_INT);
        if (!($sql->execute())) {
            $response['msg'] = $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $sql = "UPDATE `pto_cdp_detalle`
                            SET `id_user_act` = ?, `fecha_act` = ?
                        WHERE `id_pto_cdp_det` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $op, PDO::PARAM_INT);
                $sql->execute();
                $response['status'] = 'ok';
            } else {
                $response['msg'] = 'No se registró ningún nuevo dato';
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

echo json_encode($response);
