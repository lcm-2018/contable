<?php
session_start();
if (isset($_POST)) {
    //Recibir variables por POST
    $numDoc = $_POST['numDoc'];
    $tipodato = $_POST['tipodato'];
    $fecha = $_POST['fecha'];
    $id_tercero = $_POST['id_tercero'];
    $referencia = $_POST['referencia'];
    $objeto = $_POST['objeto'];
    $vigencia = $_SESSION['vigencia'];
    $id_arq = $_POST['id_arqueo'];
    $id_sede = 1;
    $id_ref = $_POST['ref_mov'] ?? 0;
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    //
    include '../../../conexion.php';

    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    } catch (Exception $e) {
        die("No se pudo conectar: " . $e->getMessage());
    }

    try {
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $cmd->beginTransaction();
        if ($_POST['id_ctb_doc'] < 1) {

            $query = $cmd->prepare("INSERT INTO ctb_doc (vigencia,id_sede, tipo_doc, id_manu,id_tercero, fecha, detalle, id_plano,id_ref, id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)");
            $query->bindParam(1, $vigencia, PDO::PARAM_INT);
            $query->bindParam(2, $id_sede, PDO::PARAM_INT);
            $query->bindParam(3, $tipodato, PDO::PARAM_STR);
            $query->bindParam(4, $numDoc, PDO::PARAM_INT);
            $query->bindParam(5, $id_tercero, PDO::PARAM_INT);
            $query->bindParam(6, $fecha, PDO::PARAM_STR);
            $query->bindParam(7, $objeto, PDO::PARAM_STR);
            $query->bindParam(8, $referencia, PDO::PARAM_INT);
            $query->bindParam(9, $id_ref, PDO::PARAM_INT);
            $query->bindParam(10, $iduser, PDO::PARAM_INT);
            $query->bindParam(11, $fecha2);
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $id = $cmd->lastInsertId();
                $response[] = array("value" => 'ok', "id" => $id);
            } else {
                $response[] = array("value" => 'error1');
                print_r($query->errorInfo()[2]);
            }
            //cambio el estado de seg_tes_causa_arqueo a 1
            if ($id_arq > 0) {
                $query = $cmd->prepare("UPDATE seg_tes_causa_arqueo SET estado = 1 WHERE id_ctb_doc = ?");
                $query->bindParam(1, $id_arq, PDO::PARAM_INT);
                $query->execute();
                if ($query->rowCount() > 0) {
                    $response[] = array("value" => 'modificado', "id" => $id);
                } else {
                    print_r($query->errorInfo()[2]);
                }
            }
        } else {
            $id = $_POST['id_ctb_doc'];
            $query = $cmd->prepare("UPDATE ctb_doc SET id_manu = ?,id_tercero=?, fecha = ?, detalle =?, id_plano=?, id_ref=?, id_usuer_act=?,fec_act=? WHERE id_ctb_doc = ?");
            $query->bindParam(1, $numDoc, PDO::PARAM_INT);
            $query->bindParam(2, $id_tercero, PDO::PARAM_INT);
            $query->bindParam(3, $fecha, PDO::PARAM_STR);
            $query->bindParam(4, $objeto, PDO::PARAM_STR);
            $query->bindParam(5, $referencia, PDO::PARAM_INT);
            $query->bindParam(6, $id_ref, PDO::PARAM_INT);
            $query->bindParam(7, $iduser, PDO::PARAM_INT);
            $query->bindParam(8, $fecha2);
            $query->bindParam(9, $id, PDO::PARAM_INT);
            $query->execute();
            if ($query->rowCount() > 0) {
                $response[] = array("value" => 'mod', "id" => $id);
            } else {
                print_r($query->errorInfo()[2]);
            }
        }
        $cmd->commit();
    } catch (Exception $e) {
        $response = null;
        $response[] = array("value" => 'error3');
        $cmd->rollBack();
        echo "Failed: " . $e->getMessage();
    }
    echo json_encode($response);
}
