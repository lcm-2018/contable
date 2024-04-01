<?php
session_start();
if (isset($_POST)) {
    //Recibir variables por POST
    $estado = $_POST['estado'] ?? 1;
    $tipo_dato = $_POST['tipodato'];
    $fecha = $_POST['fecha'];
    $numDoc = $_POST['numDoc'];
    $objeto = $_POST['objeto'] . $_POST['tipodato'];
    $datFecVigencia = $_SESSION['vigencia'];
    $id_tercero = $_POST['id_tercero'];
    $objeto = $_POST['objeto'];
    $id_cc = 0;
    $id_codigoCta = $_POST['id_codigoCta'];
    $valorDebito = str_replace(",", "", $_POST['valorDebito']);
    $valorCredito = str_replace(",", "", $_POST['valorCredito']);
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    //
    include '../../../conexion.php';
    if ($estado == 0) {
        // Ejecutar para información general ----> se ejecuta cuando hay cambios
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            if (empty($_POST['id_ctb_doc'])) {
                $query = $cmd->prepare("INSERT INTO ctb_doc (vigencia, tipo_doc, id_manu,id_tercero, fecha, detalle, id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $query->bindParam(1, $datFecVigencia, PDO::PARAM_INT);
                $query->bindParam(2, $tipo_dato, PDO::PARAM_STR);
                $query->bindParam(3, $numDoc, PDO::PARAM_INT);
                $query->bindParam(4, $id_tercero, PDO::PARAM_INT);
                $query->bindParam(5, $fecha, PDO::PARAM_STR);
                $query->bindParam(6, $objeto, PDO::PARAM_STR);
                $query->bindParam(7, $iduser, PDO::PARAM_INT);
                $query->bindParam(8, $fecha2);
                $query->execute();
                if ($cmd->lastInsertId() > 0) {
                    $id = $cmd->lastInsertId();
                } else {
                    print_r($query->errorInfo()[2]);
                }
                $cmd = null;
            } else {
                $id = $_POST['id_ctb_doc'];
                $query = $cmd->prepare("UPDATE pto_documento SET id_manu = :id_manu, fecha = :fecha, objeto =:objeto, id_usuer_act=:id_usuer_act,fec_act=:fec_act WHERE id_pto_doc = :id_pto_doc");
                $query->bindParam(":id_manu", $id_manu);
                $query->bindParam(":fecha", $fecha);
                $query->bindParam(":objeto", $objeto);
                $query->bindParam(":id_usuer_act", $iduser);
                // $query->bindParam(":fec_act", $date);
                $query->bindParam(":id_pto_doc", $id);
                // $query->execute();
                $cmd = null;
                $response[] = array("value" => 'modificado', "id" => $id);
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    // Ejecutar para movimiento de detalle ----> siempre se ejecuta
    if ($_POST['id_ctb_doc'] == 0) $id_ctb_doc =  $id;
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        if (empty($_POST['id_ctb_doc2'])) {
            $query = $cmd->prepare("INSERT INTO ctb_libaux (id_ctb_doc,id_cc,cuenta,debito,credito,documento,id_user_reg,fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $query->bindParam(1, $id_ctb_doc, PDO::PARAM_INT);
            $query->bindParam(2, $id_cc, PDO::PARAM_INT);
            $query->bindParam(3, $id_codigoCta, PDO::PARAM_STR);
            $query->bindParam(4, $valorDebito, PDO::PARAM_STR);
            $query->bindParam(5, $valorCredito, PDO::PARAM_INT);
            $query->bindParam(6, $documento, PDO::PARAM_STR);
            $query->bindParam(7, $iduser, PDO::PARAM_INT);
            $query->bindParam(8, $fecha2);
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $id2 = $cmd->lastInsertId();
                $response[] = array("value" => 'ok', "id" => $id, "detalle" => 'ok', "id2" => $id2);
            } else {
                print_r($query->errorInfo()[2]);
            }
            $cmd = null;
        } else {
            // Para editar el movimiento
            $id = $_POST['id_ctb_doc'];
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
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    echo json_encode($response);
}
