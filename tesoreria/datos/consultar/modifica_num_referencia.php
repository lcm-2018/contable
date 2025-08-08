<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../terceros.php';
include '../../../financiero/consultas.php';

$id = $_POST['id_referencia'];
$referencia = $_POST['numRef'];
$banco = $_POST['banco'];
$fech_doc = $_POST['fecha'];

$fecha = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $fecha->format('Y-m-d H:i:s');
$id_user = $_SESSION['id_user'];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 56, $cmd);


$response['status'] = 'error';
if ($fech_doc <= $fecha_cierre) {
    $response['msg'] = 'La fecha de la referencia no puede ser anterior a la fecha de cierre del período';
    echo json_encode($response);
    exit();
}

try {

    if ($id > 0) {
        $sql = "UPDATE `tes_referencia`
            SET `numero` = ?, `id_tes_cuenta` = ?, `fecha` = ?
            WHERE `id_referencia` = ?";
        $stmt = $cmd->prepare($sql);
        $stmt->bindParam(1, $referencia, PDO::PARAM_STR);
        $stmt->bindParam(2, $banco, PDO::PARAM_INT);
        $stmt->bindParam(3, $fech_doc, PDO::PARAM_STR);
        $stmt->bindParam(4, $id, PDO::PARAM_INT);
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
        $sql = "INSERT INTO `tes_referencia` (`numero`, `fec_reg`, `id_user_reg`,`estado`, `id_tes_cuenta`, `fecha`)
            VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $cmd->prepare($sql);
        $stmt->bindParam(1, $referencia, PDO::PARAM_STR);
        $stmt->bindParam(2, $fecha2, PDO::PARAM_STR);
        $stmt->bindParam(3, $id_user, PDO::PARAM_INT);
        $stmt->bindParam(4, $estado, PDO::PARAM_INT);
        $stmt->bindParam(5, $banco, PDO::PARAM_INT);
        $stmt->bindParam(6, $fech_doc, PDO::PARAM_STR);
        if (!($stmt->execute())) {
            $response['msg'] = 'Error al crear el número de referencia ' . $stmt->errorInfo()[2];
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
