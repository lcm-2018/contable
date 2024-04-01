<?php
session_start();
if (isset($_POST)) {
    //Recibir variables por POST
    $estado = $_POST['estado'] ?? 1;
    $id = $_POST['id_editar'];
    $id_ctb_doc = $_POST['id_ctb_doc'];
    $id_tercero = $_POST['id_tercero'];
    $id_crp = $_POST['id_crpp'];
    $id_codigoCta = $_POST['id_codigoCta'];
    $valorDebito = str_replace(",", "", $_POST['valorDebito']);
    $valorCredito = str_replace(",", "", $_POST['valorCredito']);
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
        if (empty($_POST['id_editar'])) {
            $query = $cmd->prepare("INSERT INTO ctb_libaux (id_ctb_doc,cuenta,debito,credito,id_crp,id_tercero,id_user_reg,fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?,?)");
            $query->bindParam(1, $id_ctb_doc, PDO::PARAM_INT);
            $query->bindParam(2, $id_codigoCta, PDO::PARAM_STR);
            $query->bindParam(3, $valorDebito, PDO::PARAM_STR);
            $query->bindParam(4, $valorCredito, PDO::PARAM_STR);
            $query->bindParam(5, $id_crp, PDO::PARAM_INT);
            $query->bindParam(6, $id_tercero, PDO::PARAM_INT);
            $query->bindParam(7, $iduser, PDO::PARAM_INT);
            $query->bindParam(8, $fecha2);
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $id = $cmd->lastInsertId();
                $response[] = array("value" => 'ok', "id" => $id);
            } else {
                print_r($query->errorInfo()[2]);
            }
        } else {
            // Para editar el movimiento
            $query = $cmd->prepare("UPDATE ctb_libaux SET cuenta=?, debito=?, credito=?, id_usuer_act=?, fec_act=? WHERE id_ctb_libaux = ?");
            $query->bindParam(1, $id_codigoCta, PDO::PARAM_STR);
            $query->bindParam(2, $valorDebito, PDO::PARAM_STR);
            $query->bindParam(3, $valorCredito, PDO::PARAM_STR);
            $query->bindParam(4, $iduser, PDO::PARAM_INT);
            $query->bindParam(5, $fecha2);
            $query->bindParam(6, $id, PDO::PARAM_INT);
            $query->execute();
            if ($query->rowCount() > 0) {
                $response[] = array("value" => 'mod', "id" => $id);
            } else {
                print_r($query->errorInfo()[2]);
            }
            $cmd = null;
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    echo json_encode($response);
}
