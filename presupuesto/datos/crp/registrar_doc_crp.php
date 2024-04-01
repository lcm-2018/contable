<?php
session_start();
if (isset($_POST)) {
    //Recibir variables por POST
    $id_pto = $_POST['id_pto'];
    $id_doc = $_POST['id_doc'];
    $fecha = $_POST['fecha'];
    $fecha = date('Y-m-d', strtotime($fecha));
    $datFecVigencia = $_POST['datFecVigencia'];
    $numCrp = $_POST['numCrp'];
    $id_tercero = $_POST['id_tercero'];
    $objeto = $_POST['objeto'];
    $tipo_mov = 'CRP';
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    //
    include '../../../conexion.php';
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        if (empty($_POST['id_pto_doc'])) {
            $query = $cmd->prepare("INSERT INTO pto_documento (id_pto_presupuestos, tipo_doc, id_manu,id_tercero, fecha, objeto, id_user_reg, fec_reg,id_auto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $query->bindParam(1, $id_pto, PDO::PARAM_INT);
            $query->bindParam(2, $tipo_mov, PDO::PARAM_STR);
            $query->bindParam(3, $numCrp, PDO::PARAM_INT);
            $query->bindParam(4, $id_tercero, PDO::PARAM_INT);
            $query->bindParam(5, $fecha, PDO::PARAM_STR);
            $query->bindParam(6, $objeto, PDO::PARAM_STR);
            $query->bindParam(7, $iduser, PDO::PARAM_INT);
            $query->bindParam(8, $fecha2);
            $query->bindParam(9, $id_doc, PDO::PARAM_INT);
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $id = $cmd->lastInsertId();
                $response[] = array("value" => 'ok', "id" => $id);
            } else {
                print_r($query->errorInfo()[2]);
            }
            $cmd = null;
        } else {
            $id = $_POST['id_pto_doc'];
            $query = $cmd->prepare("UPDATE pto_documento SET id_manu = :id_manu, fecha = :fecha, objeto =:objeto, id_usuer_act=:id_usuer_act,fec_act=:fec_act WHERE id_pto_doc = :id_pto_doc");
            $query->bindParam(":id_manu", $id_manu);
            $query->bindParam(":fecha", $fecha);
            $query->bindParam(":objeto", $objeto);
            $query->bindParam(":id_usuer_act", $iduser);
            $query->bindParam(":fec_act", $date);
            $query->bindParam(":id_pto_doc", $id);
            $query->execute();
            $cmd = null;
            $response[] = array("value" => 'modificado', "id" => $id);
        }
        echo json_encode($response);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
