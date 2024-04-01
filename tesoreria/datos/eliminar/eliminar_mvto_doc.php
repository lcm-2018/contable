<?php
$_post = json_decode(file_get_contents('php://input'), true);
$id = $_post['id'];

include '../../../conexion.php';
// Realizo conexion con la base de datos
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
} catch (Exception $e) {
    die("No se pudo conectar: " . $e->getMessage());
}
// Incio la transaccion
try {
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $cmd->beginTransaction();

    try {
        $query = $cmd->prepare("DELETE FROM ctb_doc WHERE id_ctb_doc = ?");
        $query->bindParam(1, $id);
        $query->execute();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    try {
        $query = $cmd->prepare("DELETE FROM pto_documento_detalles WHERE id_ctb_doc = ?");
        $query->bindParam(1, $id);
        $query->execute();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    $response[] = array("value" => "ok");
    $cmd->commit();
} catch (Exception $e) {
    $cmd->rollBack();
    $response[] = array("value" => "no");
}
echo json_encode($response);
