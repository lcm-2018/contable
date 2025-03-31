<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
//recibir datos des un fetch en js 
$id = isset($_POST['id_concepto']) ? $_POST['id_concepto'] : exit('Acceso no autorizado');
$vigencia = $_SESSION['vigencia'];
$id_vigencia = $_SESSION['id_vigencia'];
$concepto = isset($_POST['concepto']) ? $_POST['concepto'] : 0;
$valor = isset($_POST['valor']) ? $_POST['valor'] : 0;
$operacion = isset($_POST['oper']) ? $_POST['oper'] : '';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$res['status'] = 'error';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_valxvig` FROM `nom_valxvigencia` 
            WHERE `id_vigencia` = $id_vigencia AND `id_concepto` = $concepto AND `id_valxvig` <> $id";
    $rs = $cmd->query($sql);
    $resultado = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    $res['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}


try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    if (empty($resultado) && $operacion == 'add') {
        $sql = "INSERT INTO `nom_valxvigencia` (`id_vigencia`, `id_concepto`, `valor`, `fec_reg`)
                VALUES (?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_vigencia, PDO::PARAM_INT);
        $sql->bindParam(2, $concepto, PDO::PARAM_INT);
        $sql->bindParam(3, $valor, PDO::PARAM_STR);
        $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $res['status'] =  'ok';
        } else {
            $res['msg'] =  $sql->errorInfo()[2];
        }
    } else if (empty($resultado) && $operacion == 'edit' && $id > 0) {
        $sql = "UPDATE `nom_valxvigencia` SET `valor` = ? WHERE `id_valxvig` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $valor, PDO::PARAM_STR);
        $sql->bindParam(2, $id, PDO::PARAM_INT);
        if (!($sql->execute())) {
            $res['msg'] = $sql->errorInfo()[2];
        } else {
            if ($sql->rowCount() > 0) {
                $sql = $sql = "UPDATE `nom_valxvigencia` SET `fec_act` = ? WHERE `id_valxvig` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(2, $id, PDO::PARAM_INT);
                $sql->execute();
                $res['status'] =  'ok';
            } else {
                $res['msg'] = 'No se realizaron cambios.';
            }
        }
    } else if (empty($resultado) && $operacion == 'del') {
        $sql = "DELETE FROM `nom_valxvigencia` WHERE `id_valxvig` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id, PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            include '../../../financiero/reg_logs.php';
            $ruta = '../../../log';
            $consulta = "DELETE FROM `nom_valxvigencia` WHERE `id_valxvig` = $id";
            RegistraLogs($ruta, $consulta);
            $res['status'] =  'ok';
        } else {
            $res['msg'] =  $sql->errorInfo()[2];
        }
    } else {
        $res['msg'] = 'Revisar. No se realizó ninguna operación.';
    }
    $cmd = null;
} catch (PDOException $e) {
    $res['msg'] =  $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

echo json_encode($res);
