<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_pgcp = isset($_POST['id_pgcp']) ? $_POST['id_pgcp'] : exit('Acceso no autorizado');
$cuentas = $_POST['cuentas'];
$nombre = $_POST['nombre'];
$tipo = $_POST['tipo'];
$numero = $_POST['numero'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');

$response['value'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($id_pgcp == 0) {
        $estado = 1;
        $query = "INSERT INTO `ctb_pgcp` (`cuenta`, `nombre`, `tipo_dato`, `estado`,`id_user_reg`, `fec_reg`) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $cuentas, PDO::PARAM_STR);
        $query->bindParam(2, $nombre, PDO::PARAM_STR);
        $query->bindParam(3, $tipo, PDO::PARAM_STR);
        $query->bindParam(4, $estado, PDO::PARAM_INT);
        $query->bindParam(5, $iduser, PDO::PARAM_INT);
        $query->bindParam(6, $fecha2);
        $query->execute();
        $id = $cmd->lastInsertId();
        if ($cmd->lastInsertId() > 0) {
            $response['value'] = 'ok';
            $response['msg'] = 'Correcto';
        } else {
            $response['msg'] = $query->errorInfo()[2];
        }
    } else {
        $query = "UPDATE `ctb_pgcp` SET `cuenta` = ?, `nombre` = ?, `tipo_dato` = ? WHERE `id_pgcp` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $cuentas, PDO::PARAM_INT);
        $query->bindParam(2, $nombre, PDO::PARAM_STR);
        $query->bindParam(3, $tipo, PDO::PARAM_STR);
        $query->bindParam(4, $id_pgcp, PDO::PARAM_INT);
        if (!($query->execute())) {
            $response['msg'] = $query->errorInfo()[2];
        } else {
            if ($query->rowCount() > 0) {
                $query = $query = "UPDATE `ctb_pgcp` SET `fec_act` = ?, `id_usuer_act` = ? WHERE `id_pgcp` = ?";
                $query = $cmd->prepare($query);
                $query->bindValue(1, $date->format('Y-m-d H:i:s'));
                $query->bindParam(2, $iduser, PDO::PARAM_INT);
                $query->bindParam(3, $id_pgcp, PDO::PARAM_INT);
                $query->execute();
                $response['value'] = 'ok';
            } else {
                $response['msg'] = 'No se realizó ningún cambio';
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
