<?php
session_start();
if (isset($_POST)) {
    $banco = $_POST['banco'];
    $cuentas = $_POST['cuentas'];
    $tipo_cuenta = $_POST['tipo_cuenta'];
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
    $query = $cmd->prepare("SELECT id_pgcp,nombre, cuenta FROM ctb_pgcp WHERE id_pgcp=?");
    $query->bindParam(1, $cuentas, PDO::PARAM_INT);
    $query->execute();
    $cuentacont = $query->fetch();
    $id_pgcp = $cuentacont['id_pgcp'];
    $nombre = $cuentacont['nombre'];
    $cuenta = $cuentacont['cuenta'];
    if (!empty($_POST['id_cuenta'])) {
        $id_cuenta = $_POST['id_cuenta'];
        $estado = 0;
        $query = $cmd->prepare("UPDATE tes_cuentas SET id_banco=?, id_tipo_cuenta=?, nombre=?, numero=?, cta_contable=?, estado=?,id_user_act=?, fecha_act=? WHERE id_tes_cuenta=?");
        $query->bindParam(1, $banco, PDO::PARAM_INT);
        $query->bindParam(2, $tipo_cuenta, PDO::PARAM_INT);
        $query->bindParam(3, $nombre, PDO::PARAM_STR);
        $query->bindParam(4, $numero, PDO::PARAM_STR);
        $query->bindParam(5, $cuenta, PDO::PARAM_STR);
        $query->bindParam(6, $estado, PDO::PARAM_INT);
        $query->bindParam(7, $iduser, PDO::PARAM_INT);
        $query->bindParam(8, $fecha2);
        $query->bindParam(9, $id_cuenta, PDO::PARAM_INT);
        $query->execute();
        if ($query->rowCount() > 0) {
            $response[] = array("value" => 'ok', "tipo" => 2);
        } else {
            print_r($query->errorInfo()[2]);
        }
    } else {
        $estado = 0;
        // Realizo el insert
        $query = $cmd->prepare("INSERT INTO tes_cuentas (id_banco,id_tipo_cuenta,id_pgcp,nombre,numero,cta_contable,estado,id_user_reg, fecha_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $query->bindParam(1, $banco, PDO::PARAM_INT);
        $query->bindParam(2, $tipo_cuenta, PDO::PARAM_INT);
        $query->bindParam(3, $id_pgcp, PDO::PARAM_INT);
        $query->bindParam(4, $nombre, PDO::PARAM_STR);
        $query->bindParam(5, $numero, PDO::PARAM_STR);
        $query->bindParam(6, $cuenta, PDO::PARAM_STR);
        $query->bindParam(7, $estado, PDO::PARAM_INT);
        $query->bindParam(8, $iduser, PDO::PARAM_INT);
        $query->bindParam(9, $fecha2);
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
