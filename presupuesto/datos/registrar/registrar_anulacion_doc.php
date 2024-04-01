<?php
session_start();
if (isset($_POST)) {
    $fecha = $_POST['fecha'];
    $objeto = $_POST['objeto'];
    $id_pto_doc = $_POST['id_pto_doc'];
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    include '../../../conexion.php';
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    // Consultar el tipo de documento para realizar el registro en la tabla pto_anula
    $query = $cmd->prepare("SELECT tipo_doc FROM pto_documento WHERE id_pto_doc=?");
    $query->bindParam(1, $id_pto_doc, PDO::PARAM_INT);
    $query->execute();
    $tipo_doc = $query->fetch();
    $tipo_doc = $tipo_doc['tipo_doc'];
    if ($tipo_doc == 'CDP') {
        $objeto = 1;
    } elseif ($tipo_doc == 'CRP') {
        $objeto = 2;
    } else {
        $objeto = 3;
    }
    // Realizar el registro en la tabla pto_anula
    $query = $cmd->prepare("INSERT INTO pto_anula (id_pto_doc,fecha,concepto, id_user_reg, fec_reg) VALUES (?, ?, ?, ?,?)");
    $query->bindParam(1, $id_pto_doc, PDO::PARAM_INT);
    $query->bindParam(2, $fecha, PDO::PARAM_STR);
    $query->bindParam(3, $objeto, PDO::PARAM_STR);
    $query->bindParam(4, $iduser, PDO::PARAM_INT);
    $query->bindParam(5, $fecha2);
    $query->execute();
    if ($cmd->lastInsertId() > 0) {
        $id = $cmd->lastInsertId();
        // realizar un update al campo estado de la tabla sep_pto_documento
        $query = $cmd->prepare("UPDATE pto_documento SET estado=5 WHERE id_pto_doc=?");
        $query->bindParam(1, $id_pto_doc, PDO::PARAM_INT);
        $query->execute();
        // Realizar un update al campo estado de la tabla pto_documento_detalles
        $query = $cmd->prepare("UPDATE pto_documento_detalles SET estado=5 WHERE id_pto_doc=?");
        $query->bindParam(1, $id_pto_doc, PDO::PARAM_INT);
        $query->execute();
        $response[] = array("value" => 'ok', "tipo" => $objeto);
    } else {
        print_r($query->errorInfo()[2]);
    }

    $cmd = null;
    echo json_encode($response);
}
