<?php
session_start();
if (isset($_POST)) {
    //Recibir variables por POST
    $numDoc = $_POST['numDoc'];
    $tipodato = $_POST['tipodato'];
    $fecha = $_POST['fecha'];
    $id_tercero = $_POST['id_tercero'];
    $objeto = $_POST['objeto'];
    $detalle = $_POST['detalle'];
    $tipoDoc = $_POST['tipoDoc'] ?? 0;
    $numFac = $_POST['numFac'] ?? 0;
    $fechaDoc = $_POST['fechaDoc'] ?? 0;
    $fechaVen = $_POST['fechaVen'] ?? 0;
    $id_crpp = $_POST['id_crpp'];
    $valor = $_POST['valor_pagar'] ?? 0;
    $valor_iva = $_POST['valor_iva'] ?? 0;
    $valor_base = $_POST['valor_base']  ?? 0;
    $valor = str_replace(",", "", $valor);
    $valor_iva = str_replace(",", "", $valor_iva);
    $valor_base = str_replace(",", "",  $valor_base);
    $vigencia = $_SESSION['vigencia'];
    $id_sede = 1;
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
            // hacer el ultimo consecutivo guardado
            $sql = $cmd->prepare("SELECT `tipo_doc`, MAX(`id_manu`) AS id_manu FROM `ctb_doc` WHERE (`tipo_doc` ='$tipodato');");
            $sql->execute();
            $datos = $sql->fetch(PDO::FETCH_ASSOC);
            // Consulto si 
            $sq2 = $cmd->prepare("SELECT id_manu FROM ctb_doc WHERE tipo_doc='$tipodato' AND id_manu='$numDoc'");
            $sq2->execute();
            $consec = $sq2->fetch(PDO::FETCH_ASSOC);
            if ($consec['id_manu'] == $numDoc) {
                $numDoc = $datos['id_manu'] + 1;
            }
            $query = $cmd->prepare("INSERT INTO ctb_doc (vigencia,id_sede, tipo_doc, id_manu,id_tercero, fecha, detalle, id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $query->bindParam(1, $vigencia, PDO::PARAM_INT);
            $query->bindParam(2, $id_sede, PDO::PARAM_INT);
            $query->bindParam(3, $tipodato, PDO::PARAM_STR);
            $query->bindParam(4, $numDoc, PDO::PARAM_INT);
            $query->bindParam(5, $id_tercero, PDO::PARAM_INT);
            $query->bindParam(6, $fecha, PDO::PARAM_STR);
            $query->bindParam(7, $objeto, PDO::PARAM_STR);
            $query->bindParam(8, $iduser, PDO::PARAM_INT);
            $query->bindParam(9, $fecha2);
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $id = $cmd->lastInsertId();
                $response[] = array("value" => 'ok', "id" => $id);
            } else {
                $response[] = array("value" => 'error1');
                print_r($query->errorInfo()[2]);
            }
            // consulto si existe num_doc 
            if ($tipoDoc == 3) {
                $sq3 = $cmd->prepare("SELECT num_doc FROM seg_ctb_factura WHERE num_doc='$numFac' AND tipo_doc='$tipoDoc';");
                $sq3->execute();
                $consec = $sq3->fetch(PDO::FETCH_ASSOC);
                // consulta para buscar el max factura cuando documento =3
                $sql = $cmd->prepare("SELECT MAX(`num_doc`) AS num_doc FROM `seg_ctb_factura` WHERE (`tipo_doc` ='$tipoDoc');");
                $sql->execute();
                $datos = $sql->fetch(PDO::FETCH_ASSOC);
                $ultimo_fac = $datos['num_doc'];
                if ($consec['num_doc'] == $ultimo_fac) {
                    $numFac = $numFac + 1;
                }
            }

            $query = $cmd->prepare("INSERT INTO seg_ctb_factura (id_ctb_doc,id_pto_crp,tipo_doc,num_doc,fecha_fact,fecha_ven,valor_pago,valor_iva,valor_base,id_user_reg,fec_rec,detalle) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,?)");
            $query->bindParam(1, $id, PDO::PARAM_INT);
            $query->bindParam(2, $id_crpp, PDO::PARAM_INT);
            $query->bindParam(3, $tipoDoc, PDO::PARAM_STR);
            $query->bindParam(4, $numFac, PDO::PARAM_STR);
            $query->bindParam(5, $fechaDoc, PDO::PARAM_STR);
            $query->bindParam(6, $fechaVen, PDO::PARAM_STR);
            $query->bindParam(7, $valor, PDO::PARAM_STR);
            $query->bindParam(8, $valor_iva, PDO::PARAM_STR);
            $query->bindParam(9, $valor_base, PDO::PARAM_STR);
            $query->bindParam(10, $iduser, PDO::PARAM_INT);
            $query->bindParam(11, $fecha2);
            $query->bindParam(12, $detalle, PDO::PARAM_STR);
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $id2 = $cmd->lastInsertId();
                $response[] = array("value" => 'ok', "id" => $id);
            } else {
                $response[] = array("value" => 'error2');
                print_r($query->errorInfo()[2]);
            }
        } else {
            $id = $_POST['id_ctb_doc'];
            $query = $cmd->prepare("UPDATE ctb_doc SET id_manu = ?, fecha = ?, detalle =?, id_usuer_act=?,fec_act=?,id_tercero=? WHERE id_ctb_doc = ?");
            $query->bindParam(1, $numDoc, PDO::PARAM_INT);
            $query->bindParam(2, $fecha, PDO::PARAM_STR);
            $query->bindParam(3, $objeto, PDO::PARAM_STR);
            $query->bindParam(4, $iduser, PDO::PARAM_INT);
            $query->bindParam(5, $fecha2);
            $query->bindParam(6, $id_tercero, PDO::PARAM_INT);
            $query->bindParam(7, $id, PDO::PARAM_INT);
            $query->execute();
            if ($query->rowCount() > 0) {
                $response[] = array("value" => 'mod', "id" => $id);
            } else {
                print_r($query->errorInfo()[2]);
            }
            // Editar datos que estan en la tabla seg_ctb_factura
            $query = $cmd->prepare("UPDATE seg_ctb_factura SET tipo_doc = ?, num_doc = ?, fecha_fact = ?, fecha_ven = ?, valor_pago = ?, valor_iva = ?, valor_base = ?, id_user_act = ?, fec_act = ?, detalle = ? WHERE id_ctb_doc = ?");
            $query->bindParam(1, $tipoDoc, PDO::PARAM_STR);
            $query->bindParam(2, $numFac, PDO::PARAM_STR);
            $query->bindParam(3, $fechaDoc, PDO::PARAM_STR);
            $query->bindParam(4, $fechaVen, PDO::PARAM_STR);
            $query->bindParam(5, $valor, PDO::PARAM_STR);
            $query->bindParam(6, $valor_iva, PDO::PARAM_STR);
            $query->bindParam(7, $valor_base, PDO::PARAM_STR);
            $query->bindParam(8, $iduser, PDO::PARAM_INT);
            $query->bindParam(9, $fecha2);
            $query->bindParam(10, $detalle, PDO::PARAM_STR);
            $query->bindParam(11, $id, PDO::PARAM_INT);

            $query->execute();
            if ($query->rowCount() > 0) {
                $response[] = array("value" => 'modificado', "id" => $id);
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
