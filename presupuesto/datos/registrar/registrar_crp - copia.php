<?php
session_start();
if (isset($_POST)) {
    //Recibir variables por POST
    $numCdp = $_POST['numCdp'];
    $fecha = $_POST['fecha'];
    $objeto = $_POST['objeto'];
    $id_pto_cdp = $_POST['id_pto_cdp'];
    $datos = $_POST['datos'];
    $num_contrato = $_POST['contrato'];
    $obj = json_decode($datos, true);
    $id_tercero = $_POST['id_tercero']; // Pendiente solicitar informacion del api
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    $tipo_doc = 'CRP';
    $estado = 0;
    $mov = 0;
    $id_pto_presupuestos = $_POST['id_pto_presupuestos'];
    include '../../../conexion.php';
    include '../../../financiero/consultas.php';

    if (isset($_POST['id_pto_cdp'])) {
        // Guarda
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $query = $cmd->prepare("INSERT INTO pto_documento (id_pto_presupuestos,tipo_doc,id_tercero, id_manu,id_auto,fecha, objeto,num_contrato,estado,id_user_reg, fec_reg) VALUES (?, ?, ?,?, ?, ?, ?, ?, ?,?,?)");
            $query->bindParam(1, $id_pto_presupuestos, PDO::PARAM_INT);
            $query->bindParam(2, $tipo_doc, PDO::PARAM_STR);
            $query->bindParam(3, $id_tercero, PDO::PARAM_INT);
            $query->bindParam(4, $numCdp, PDO::PARAM_STR);
            $query->bindParam(5, $id_pto_cdp, PDO::PARAM_INT);
            $query->bindParam(6, $fecha, PDO::PARAM_STR);
            $query->bindParam(7, $objeto, PDO::PARAM_STR);
            $query->bindParam(8, $num_contrato, PDO::PARAM_STR);
            $query->bindParam(9, $estado, PDO::PARAM_INT);
            $query->bindParam(10, $iduser, PDO::PARAM_INT);
            $query->bindParam(11, $fecha2);
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $id = $cmd->lastInsertId();
            } else {
                throw new Exception("El registro general no fue guardado");
            }
            // Registro del movimiento de detalle
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $query = $cmd->prepare("INSERT INTO pto_documento_detalles (id_pto_doc, tipo_mov, rubro, valor,id_auto_dep,estado) VALUES (?, ?, ?, ?, ?,?)");
            $query->bindParam(1, $id, PDO::PARAM_INT);
            $query->bindParam(2, $tipo_doc, PDO::PARAM_STR);
            $query->bindParam(3, $rubro, PDO::PARAM_STR);
            $query->bindParam(4, $valore, PDO::PARAM_STR);
            $query->bindParam(5, $id_pto_cdp, PDO::PARAM_INT);
            $query->bindParam(6, $estado, PDO::PARAM_INT);
            foreach ($obj as $key => $value) {
                // Realizo la consulta para obtener el rubro
                $valore = str_replace(",", "", $value);
                $id_cdp = str_replace("lp", "", $key);
                $sql = "SELECT id_pto_doc,rubro FROM pto_documento_detalles WHERE id_pto_mvto = $id_cdp";
                $query_rubro = $cmd->prepare($sql);
                $query_rubro->execute();
                $row_rubro = $query_rubro->fetch();
                $rubro = $row_rubro['rubro'];
                // consultar saldo del rubro, si saldo es suficiente realizar el registro sino eliminar documento y 
                if ($valore > 0) {
                    $saldo = saldoCdp($id_pto_cdp, $rubro, $cmd);
                    if ($saldo >= $valore) {
                        $query->execute();
                        if ($cmd->lastInsertId() > 0) {
                            $id2 = $cmd->lastInsertId();
                        } else {
                            print_r($query->errorInfo()[2]);
                            throw new Exception("El registro de detalle no fue guardado");
                        }
                    } else {
                        $response[] = array("value" => 'error 1');
                        throw new Exception("");
                    }
                }
                $valore = 0;
            }
            $response[] = array("value" => 'ok', "id1" => $id, "id2" => $id2, "dato" => 1);
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
        } catch (Exception $e) {
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
    echo json_encode($response);
}
