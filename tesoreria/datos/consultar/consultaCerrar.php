<?php

include '../../../conexion.php';
$data = file_get_contents("php://input");
$estado = 1;
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

    $sql = "SELECT sum(debito) as debito, sum(credito) as credito FROM ctb_libaux WHERE id_ctb_doc=$data GROUP BY id_ctb_doc";
    $rs = $cmd->query($sql);
    $sumaMov = $rs->fetch();
    $dif = $sumaMov['debito'] - $sumaMov['credito'];

    $sql = "SELECT
    `cuenta`
    , `id_ctb_doc`
    FROM
    `ctb_libaux`
    WHERE (`id_ctb_doc` =$data);";
    $rs = $cmd->query($sql);
    $cuentas = $rs->fetchAll();
    foreach ($cuentas as $rp) {
        $cuenta = $rp['cuenta'];
        if ($cuenta == null) {
            $dif = 3;
        }
    }
    // Consulto el tipo_doc de la tabla ctb_doc
    $sql = "SELECT tipo_doc FROM ctb_doc WHERE id_ctb_doc=$data LIMIT 1";
    $rs = $cmd->query($sql);
    $datos = $rs->fetch();
    $tipo_doc = $datos['tipo_doc'];
    if ($tipo_doc == 'CMCN' || $tipo_doc == 'CMMT') {
        $estado = 5;
    }
    if ($dif == 0) {
        // update ctb_libaux set estado='C' where id_ctb_doc=$data;
        $query = $cmd->prepare("UPDATE ctb_doc SET estado=$estado WHERE id_ctb_doc=?");
        $query->bindParam(1, $data, PDO::PARAM_INT);
        $query->execute();
        // Actualizo el campo estado de la tabla pto_documento_detalles
        $query = $cmd->prepare("UPDATE pto_documento_detalles SET estado=0 WHERE id_ctb_doc=?");
        $query->bindParam(1, $data, PDO::PARAM_INT);
        $query->execute();
        $response[] = array("value" => "ok");
    } else {
        $response[] = array("value" => "no");
    }
    $cmd->commit();
} catch (Exception $e) {
    $cmd->rollBack();
    $response[] = array("value" => "no");
}
echo json_encode($response);
$cmd = null;
