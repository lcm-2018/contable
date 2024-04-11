<?php
$_post = json_decode(file_get_contents('php://input'), true);
include '../../../conexion.php';
$id = $_post['id'];
$pdo = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$response['value'] = 'error';
// consulto si el id de la cuenta fue utilizado en seg_fin_chequera_cont
try {
    $query = $pdo->prepare("DELETE FROM `ctb_fuente` WHERE `id_doc_fuente` = ? ");
    $query->bindParam(1, $id);
    $query->execute();
    if ($query->rowCount() > 0) {
        $response['value'] = 'ok';
        $response['msg'] = 'Cuenta eliminada correctamente';
    } else {
        $response['msg'] = $query->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

echo json_encode($response);
