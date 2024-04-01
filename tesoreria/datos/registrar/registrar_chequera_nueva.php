<?php
session_start();
if (isset($_POST)) {
    $fecha = $_POST['fecha'];
    $banco = $_POST['banco'];
    $cuentas = $_POST['cuentas'];
    $num_chequera = $_POST['num_chequera'];
    $inicial = $_POST['inicial'];
    $maximo = $_POST['maximo'];
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    include '../../../conexion.php';
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    // Verifico si la variable $_POST['id_chequera'] tiene valor

    if (isset($_POST['id_chequera'])) {
        $id_chequera = $_POST['cuentas'];
        $query = $cmd->prepare("UPDATE seg_fin_chequeras SET id_cuenta=?, numero=?, fecha=?, inicial=?, maximo=?, id_user_act=?, fec_act=? WHERE id_chequera=?");
        $query->bindParam(1, $cuentas, PDO::PARAM_INT);
        $query->bindParam(2, $num_chequera, PDO::PARAM_STR);
        $query->bindParam(3, $fecha, PDO::PARAM_STR);
        $query->bindParam(4, $inicial, PDO::PARAM_INT);
        $query->bindParam(5, $maximo, PDO::PARAM_STR);
        $query->bindParam(6, $iduser, PDO::PARAM_INT);
        $query->bindParam(7, $fecha2);
        $query->bindParam(8, $id_chequera, PDO::PARAM_INT);
        $query->execute();
        if ($query->rowCount() > 0) {
            $response[] = array("value" => 'ok', "tipo" => 2);
        } else {
            print_r($query->errorInfo()[2]);
        }
    } else {
        $query = $cmd->prepare("INSERT INTO seg_fin_chequeras (id_cuenta,numero,fecha,inicial,maximo, id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $query->bindParam(1, $cuentas, PDO::PARAM_INT);
        $query->bindParam(2, $num_chequera, PDO::PARAM_STR);
        $query->bindParam(3, $fecha, PDO::PARAM_STR);
        $query->bindParam(4, $inicial, PDO::PARAM_INT);
        $query->bindParam(5, $maximo, PDO::PARAM_STR);
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
