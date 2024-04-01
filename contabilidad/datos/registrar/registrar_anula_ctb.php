<?php
session_start();
if (isset($_POST)) {
    $fecha = $_POST['fecha'];
    $objeto = $_POST['objeto'];
    $id_ctb_doc = $_POST['id_pto_doc'];
    $numero = $_POST['numero'];
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    include '../../../conexion.php';
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    // Consultar el tipo de documento para realizar el registro en la tabla pto_anula
    $query = $cmd->prepare("SELECT tipo_doc FROM ctb_doc WHERE id_ctb_doc=?");
    $query->bindParam(1, $id_ctb_doc, PDO::PARAM_INT);
    $query->execute();
    $tipo_doc = $query->fetch();
    $tipo_doc = $tipo_doc['tipo_doc'];
    $id_modulo = 3;
    // Realizar el registro en la tabla pto_anula
    $query = $cmd->prepare("INSERT INTO seg_ctb_anula (id_ctb_doc,tipo_doc,id_modulo,id_manu_anula,fecha,concepto, id_user_reg, fec_reg) VALUES (?,?, ?, ?, ?,?,?,?)");
    $query->bindParam(1, $id_ctb_doc, PDO::PARAM_INT);
    $query->bindParam(2, $tipo_doc, PDO::PARAM_STR);
    $query->bindParam(3, $id_modulo, PDO::PARAM_INT);
    $query->bindParam(4, $numero, PDO::PARAM_INT);
    $query->bindParam(5, $fecha, PDO::PARAM_STR);
    $query->bindParam(6, $objeto, PDO::PARAM_STR);
    $query->bindParam(7, $iduser, PDO::PARAM_INT);
    $query->bindParam(8, $fecha2);
    $query->execute();
    if ($cmd->lastInsertId() > 0) {
        $id = $cmd->lastInsertId();
        // realizar un update al campo estado de la tabla sep_pto_documento
        $query = $cmd->prepare("UPDATE ctb_doc SET estado=5 WHERE id_ctb_doc=?");
        $query->bindParam(1, $id_ctb_doc, PDO::PARAM_INT);
        $query->execute();
        // realiza un update al campo estado de la tabla pto_documento_detalles
        $query = $cmd->prepare("UPDATE pto_documento_detalles SET estado=5 WHERE id_ctb_doc=?");
        $query->bindParam(1, $id_ctb_doc, PDO::PARAM_INT);
        $query->execute();
        $response[] = array("value" => 'ok', "tipo" => 1);
    } else {
        print_r($query->errorInfo()[2]);
    }

    $cmd = null;
    echo json_encode($response);
}
