<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}

$id_pto_doc = isset($_POST['id_pto_doc']) ? $_POST['id_pto_doc'] : exit('Acceso no disponible');
$fecha = $_POST['fecha'];
$motivo = $_POST['objeto'];
$iduser = $_SESSION['id_user'];
$estado = 0;
include '../../../conexion.php';
$response['status'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `ctb_doc`
                    SET `id_user_anula` = ?,`fecha_anula` = ?,`concepto_anula` = ?, `estado` = ?
                WHERE `id_ctb_doc`  = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $iduser, PDO::PARAM_INT);
    $sql->bindParam(2, $fecha, PDO::PARAM_STR);
    $sql->bindParam(3, $motivo, PDO::PARAM_STR);
    $sql->bindParam(4, $estado, PDO::PARAM_INT);
    $sql->bindParam(5, $id_pto_doc, PDO::PARAM_INT);
    if ($sql->execute()) {
        $sql2 = "UPDATE `pto_rec`
                    SET `estado` = 0
                WHERE `id_ctb_doc` = ?";
        $sql2 = $cmd->prepare($sql2);
        $sql2->bindParam(1, $id_pto_doc, PDO::PARAM_INT);
        $sql2->execute();
        $response['status'] = 'ok';
    } else {
        $response['msg'] = $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
