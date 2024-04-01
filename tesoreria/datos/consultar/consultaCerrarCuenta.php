<?php

include '../../../conexion.php';
$data = file_get_contents("php://input");
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

    $sql = "SELECT id_pgcp from seg_tes_cuentas WHERE id_tes_cuenta=$data";
    $rs = $cmd->query($sql);
    $cuenta = $rs->fetch();
    $id_pgcp = $cuenta['id_pgcp'];
    // update ctb_libaux set estado='C' where id_ctb_doc=$data;
    $query = $cmd->prepare("UPDATE seg_tes_cuentas SET estado=1 WHERE id_tes_cuenta=?");
    $query->bindParam(1, $data, PDO::PARAM_INT);
    $query->execute();
    // Actualizo el campo estado de la tabla pto_documento_detalles
    $query = $cmd->prepare("UPDATE seg_ctb_pgcp SET estado=1 WHERE id_pgcp=?");
    $query->bindParam(1, $id_pgcp, PDO::PARAM_INT);
    $query->execute();
    $response[] = array("value" => "ok");
    $cmd->commit();
} catch (Exception $e) {
    $cmd->rollBack();
    $response[] = array("value" => $sql);
}
echo json_encode($response);
$cmd = null;
