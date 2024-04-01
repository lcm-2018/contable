<?php

include '../../../conexion.php';
$data = file_get_contents("php://input");
// update ctb_libaux set estado='C' where id_ctb_doc=$data;
// Realizo conexion con la base de datos
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
} catch (Exception $e) {
    die("No se pudo conectar: " . $e->getMessage());
}
// Verificar si la causación ya fue pagada
$sql = "SELECT id_ctb_cop FROM pto_documento_detalles WHERE id_ctb_cop=?";
$query = $cmd->prepare($sql);
$query->bindParam(1, $data, PDO::PARAM_INT);
$query->execute();
$causacion = $query->fetchAll();
// contar cuantos registros hay
$contar = count($causacion);
if ($contar > 0) {
    $response[] = array("estado" => "pagado");
} else {
    try {
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $cmd->beginTransaction();

        $query = $cmd->prepare("UPDATE ctb_doc SET estado=0 WHERE id_ctb_doc=?");
        $query->bindParam(1, $data, PDO::PARAM_INT);
        $query->execute();
        // Actualizo el campo estado de la tabla pto_documento_detalles
        $query = $cmd->prepare("UPDATE pto_documento_detalles SET estado=3 WHERE id_ctb_doc=?");
        $query->bindParam(1, $data, PDO::PARAM_INT);
        $query->execute();
        $response[] = array("value" => "ok");
        $cmd->commit();
    } catch (Exception $e) {
        $cmd->rollBack();
        $response[] = array("value" => "no");
    }
}
echo json_encode($response);
$cmd = null;
