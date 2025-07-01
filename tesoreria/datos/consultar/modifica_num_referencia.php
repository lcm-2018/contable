<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../terceros.php';

$id = $_POST['id_referencia'];
$referencia = $_POST['numRef'];
$banco = $_POST['banco'];
$fecha = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $fecha->format('Y-m-d H:i:s');
$id_user = $_SESSION['id_user'];

$response['status'] = 'error';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    if ($id > 0) {
        $sql = "UPDATE `tes_referencia`
            SET `numero` = ?, `id_tes_cuenta` = ?
            WHERE `id_referencia` = ?";
        $stmt = $cmd->prepare($sql);
        $stmt->bindParam(1, $referencia, PDO::PARAM_STR);
        $stmt->bindParam(2, $banco, PDO::PARAM_INT);
        $stmt->bindParam(3, $id, PDO::PARAM_INT);
        if (!($stmt->execute())) {
            $response['msg'] = 'Error al actualizar el número de referencia';
        } else {
            if ($stmt->rowCount() > 0) {
                $sq2 = "UPDATE `tes_referencia` SET `fec_act` = ?, `id_user_act` = ? WHERE `id_referencia` = ?";
                $stmt2 = $cmd->prepare($sq2);
                $stmt2->bindParam(1, $fecha2, PDO::PARAM_STR);
                $stmt2->bindParam(2, $id_user, PDO::PARAM_INT);
                $stmt2->bindParam(3, $id, PDO::PARAM_INT);
                $stmt2->execute();
                $response['status'] = 'ok';
            } else {
                $response['msg'] = 'No se realizaron cambios en el número de referencia';
            }
        }
    } else {
        $estado = 1;
        $sql = "INSERT INTO `tes_referencia` (`numero`, `fec_reg`, `id_user_reg`,`estado`, `id_tes_cuenta`)
            VALUES (?, ?, ?, ?, ?)";
        $stmt = $cmd->prepare($sql);
        $stmt->bindParam(1, $referencia, PDO::PARAM_STR);
        $stmt->bindParam(2, $fecha2, PDO::PARAM_STR);
        $stmt->bindParam(3, $id_user, PDO::PARAM_INT);
        $stmt->bindParam(4, $estado, PDO::PARAM_INT);
        $stmt->bindParam(5, $banco, PDO::PARAM_INT);
        if (!($stmt->execute())) {
            $response['msg'] = 'Error al crear el número de referencia';
        } else {
            $response['status'] = 'ok';
        }
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
} finally {
    if (isset($cmd)) {
        $cmd = null;
    }
}
echo json_encode($response);
