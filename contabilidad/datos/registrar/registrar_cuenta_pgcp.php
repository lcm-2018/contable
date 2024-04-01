<?php
session_start();
if (isset($_POST)) {
    $id_pgcp = $_POST['id_pgcp'];
    $cuentas = $_POST['cuentas'];
    $nombre = $_POST['nombre'];
    $tipo = $_POST['tipo'];
    $numero = $_POST['numero'];
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    include '../../../conexion.php';
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    // consultar el nombre y numero de cuenta de seg_pgcp
    if (!empty($_POST['id_cuenta'])) {
        $cuentas = $_POST['id_pgcp'];
    }
    if (!empty($_POST['id_pgcp'])) {
        $id_pgcp = $_POST['id_pgcp'];
        $estado = 0;
        $query = $cmd->prepare("UPDATE seg_ctb_pgcp SET cuenta=?, nombre=?, tipo_dato=?, nivel=?,id_usuer_act=?, fec_act=? WHERE id_pgcp=?");
        $query->bindParam(1, $cuentas, PDO::PARAM_INT);
        $query->bindParam(2, $nombre, PDO::PARAM_STR);
        $query->bindParam(3, $tipo, PDO::PARAM_STR);
        $query->bindParam(4, $numero, PDO::PARAM_INT);
        $query->bindParam(5, $iduser, PDO::PARAM_INT);
        $query->bindParam(6, $fecha2);
        $query->bindParam(7, $id_pgcp, PDO::PARAM_INT);
        $query->execute();
        if ($query->rowCount() > 0) {
            $response[] = array("value" => 'ok', "tipo" => 2);
        } else {
            print_r($query->errorInfo()[2]);
        }
    } else {
        $estado = 0;
        // Realizo el insert
        $query = $cmd->prepare("INSERT INTO seg_ctb_pgcp (cuenta, nombre, tipo_dato, nivel,estado,id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $query->bindParam(1, $cuentas, PDO::PARAM_STR);
        $query->bindParam(2, $nombre, PDO::PARAM_STR);
        $query->bindParam(3, $tipo, PDO::PARAM_STR);
        $query->bindParam(4, $numero, PDO::PARAM_INT);
        $query->bindParam(5, $estado, PDO::PARAM_INT);
        $query->bindParam(6, $iduser, PDO::PARAM_INT);
        $query->bindParam(7, $fecha2);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $id = $cmd->lastInsertId();
            $response[] = array("value" => 'ok', "tipo" => 1);
        } else {
            print_r($query->errorInfo()[2]);
        }
    }
    $cmd = null;
    echo json_encode($response);
}
