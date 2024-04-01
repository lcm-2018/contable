<?php
session_start();
if (isset($_POST)) {
    //Recibir variables por POST
    $id_pto_doc = $_POST['id_pto_doc'];
    $id_pto_apl = $_POST['id_pto_apl'];
    $numApl = $_POST['numApl'];
    $tipo_acto = $_POST['tipo_acto'];
    $fecha = $_POST['fecha'];
    $objeto = $_POST['objeto'];
    $id_rubroCod = $_POST['id_rubroCod'];
    $estado = isset($_POST['estado']) ? $_POST['estado'] : "detalle";
    $valorDeb = str_replace(",", "", $_POST['valorDeb']);
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    $tipo_doc = 'DES';
    $stado = 0;
    $mov = 0;
    $stado2 = 0;
    $id_pto_presupuestos = 2;
    include '../../../conexion.php';
    if (isset($_POST['estado']) && empty($_POST['id_pto_apl'])) {
        // Guarda
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $query = $cmd->prepare("INSERT INTO pto_documento (tipo_doc, id_manu,fecha, objeto, tipo_mod,estado ,id_pto_presupuestos,id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)");
            $query->bindParam(1, $tipo_doc, PDO::PARAM_STR);
            $query->bindParam(2, $numApl, PDO::PARAM_INT);
            $query->bindParam(3, $fecha, PDO::PARAM_STR);
            $query->bindParam(4, $objeto, PDO::PARAM_STR);
            $query->bindParam(5, $tipo_acto, PDO::PARAM_INT);
            $query->bindParam(6, $stado, PDO::PARAM_INT);
            $query->bindParam(7, $id_pto_presupuestos, PDO::PARAM_INT);
            $query->bindParam(8, $iduser, PDO::PARAM_INT);
            $query->bindParam(9, $fecha2);
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $id = $cmd->lastInsertId();
            } else {
                throw new Exception("El registro general no fue guardado");
            }
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $query = $cmd->prepare("INSERT INTO pto_documento_detalles (id_pto_doc, tipo_mov, mov, rubro, valor,estado,id_auto_dep) VALUES (?, ?, ?, ?, ?, ?,?)");
            $query->bindParam(1, $id, PDO::PARAM_INT);
            $query->bindParam(2, $tipo_doc, PDO::PARAM_STR);
            $query->bindParam(3, $mov, PDO::PARAM_INT);
            $query->bindParam(4, $id_rubroCod, PDO::PARAM_STR);
            $query->bindParam(5, $valorDeb, PDO::PARAM_INT);
            $query->bindParam(6, $stado2, PDO::PARAM_INT);
            $query->bindParam(7, $id_pto_doc, PDO::PARAM_INT);
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $id2 = $cmd->lastInsertId();
            } else {
                throw new Exception("El registro de movimiento no fue guardado");
            }
            $response[] = array("value" => 'ok', "id1" => $id, "id2" => $id2, "dato" => 1);
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
        } catch (\Exception $e) {
            echo $e->getMessage();
            if ($id) {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $query = $cmd->prepare("DELETE FROM pto_documento WHERE id_pto_doc = ?");
                $query->bindParam(1, $id, PDO::PARAM_INT);
                $query->execute();
            }
        } finally {
            $cmd = null;
        }
    }
    if ($estado == 'detalle') {
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $query = $cmd->prepare("INSERT INTO pto_documento_detalles (id_pto_doc, tipo_mov, mov, rubro, valor,estado,id_auto_dep) VALUES (?, ?, ?, ?, ?, ?,?)");
            $query->bindParam(1, $id_pto_apl, PDO::PARAM_INT);
            $query->bindParam(2, $tipo_doc, PDO::PARAM_STR);
            $query->bindParam(3, $mov, PDO::PARAM_INT);
            $query->bindParam(4, $id_rubroCod, PDO::PARAM_STR);
            $query->bindParam(5, $valorDeb, PDO::PARAM_INT);
            $query->bindParam(6, $stado2, PDO::PARAM_INT);
            $query->bindParam(7, $id_pto_doc, PDO::PARAM_INT);
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $id2 = $cmd->lastInsertId();
                $response[] = array("value" => 'ok', "id1" => $id_pto_apl, "id2" => $id2, "dato" => 2);
            } else {
                print_r($query->errorInfo()[2]);
                throw new Exception("El registro de movimiento no fue guardado");
            }
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
        } finally {
            $cmd = null;
        }
    }
    // Si edita la parte general del documento
    if (isset($_POST['estado']) && !empty($_POST['id_pto_apl'])) {
        // Guarda
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $query = $cmd->prepare("UPDATE pto_documento SET id_manu =?, tipo_mod = ?, fecha =?, objeto=?,id_user_act=?,fec_act=? WHERE id_pto_doc = ?");
            $query->bindParam(1, $numApl, PDO::PARAM_INT);
            $query->bindParam(2, $tipo_acto, PDO::PARAM_INT);
            $query->bindParam(3, $fecha, PDO::PARAM_STR);
            $query->bindParam(4, $objeto, PDO::PARAM_STR);
            $query->bindParam(5, $iduser, PDO::PARAM_INT);
            $query->bindParam(6, $fecha2);
            $query->bindParam(7, $id_pto_apl, PDO::PARAM_INT);
            $query->execute();
            $sql2 = $query->execute();
            if (!($query->execute())) {
                print_r($query->errorInfo()[2]);
                throw new Exception("El registro general no fue editado");
            }
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $query = $cmd->prepare("INSERT INTO pto_documento_detalles (id_pto_doc, tipo_mov, mov, rubro, valor,estado,id_auto_dep) VALUES (?, ?, ?, ?, ?, ?,?)");
            $query->bindParam(1, $id_pto_apl, PDO::PARAM_INT);
            $query->bindParam(2, $tipo_doc, PDO::PARAM_STR);
            $query->bindParam(3, $mov, PDO::PARAM_INT);
            $query->bindParam(4, $id_rubroCod, PDO::PARAM_STR);
            $query->bindParam(5, $valorDeb, PDO::PARAM_INT);
            $query->bindParam(6, $stado2, PDO::PARAM_INT);
            $query->bindParam(7, $id_pto_doc, PDO::PARAM_INT);
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $id2 = $cmd->lastInsertId();
            } else {
                print_r($query->errorInfo()[2]);
                throw new Exception("El registro de movimiento no fue guardado 2");
            }
            $response[] = array("value" => 'ok', "id1" =>  $id_pto_apl, "id2" => $id2, "dato" => $sql2);
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
        } catch (\Exception $e) {
            echo $e->getMessage();
        } finally {
            $cmd = null;
        }
    }
    echo json_encode($response);
}
