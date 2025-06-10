<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../terceros.php';
$id = $_POST['id_ctb_doc'];
$id_resolucion = $_POST['id_resol'];
$id_vigencia = $_SESSION['id_vigencia'];
$consecutivo = $_POST['numResolucion'];
$response['status'] = 'error';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_resol`,`consecutivo`
            FROM `tes_resolucion_pago`
            WHERE `id_vigencia` = $id_vigencia AND `consecutivo` = $consecutivo AND `id_ctb_doc` <> $id";
    $rs = $cmd->query($sql);
    $data = $rs->fetch(PDO::FETCH_ASSOC);
    if (!empty($data)) {
        $response['msg'] = 'El número de resolución ya existe';
        echo json_encode($response);
        exit();
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "UPDATE `tes_resolucion_pago`
            SET `consecutivo` = ?
            WHERE `id_ctb_doc` = ?";
    $stmt = $cmd->prepare($sql);
    $stmt->bindParam(1, $consecutivo, PDO::PARAM_STR);
    $stmt->bindParam(2, $id, PDO::PARAM_INT);
    if (!($stmt->execute())) {
        $response['msg'] = 'Error al actualizar el número de resolución';
    } else {
        if ($stmt->rowCount() > 0) {
            $response['status'] = 'ok';
            $response['msg'] = 'Número de resolución actualizado correctamente';
        } else {
            $response['msg'] = 'No se realizaron cambios en el número de resolución';
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
