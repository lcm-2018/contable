<?php
$_post = json_decode(file_get_contents('php://input'), true);
$id = $_post['id'];
include '../../../conexion.php';
$response['status'] = 'error';
try {
    $pdo = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $query = "DELETE FROM `ctb_libaux` WHERE `id_ctb_libaux` = ?";
    $query = $pdo->prepare($query);
    $query->bindParam(1, $id);
    $query->execute();
    if ($query->rowCount() > 0) {
        $response['status'] = 'ok';
    } else {
        $response['msg'] = 'No se eliminó ningún registro';
    }
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
