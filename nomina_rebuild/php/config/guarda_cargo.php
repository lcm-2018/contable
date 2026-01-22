<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$id_cargo = isset($_POST['id_cargo']) ? $_POST['id_cargo'] : exit('Acceso denegado');
$id_codigo = isset($_POST['slcCodigo']) ? $_POST['slcCodigo'] : 0;
$cargo = isset($_POST['txtNomCargo']) ? $_POST['txtNomCargo'] : 0;
$grado = isset($_POST['numGrado']) ? $_POST['numGrado'] : 0;
$perfil = isset($_POST['txtPerfilSiho']) ? $_POST['txtPerfilSiho'] : 0;
$id_nombramiento = isset($_POST['slcNombramiento']) ? $_POST['slcNombramiento'] : 0;
$operacion = $_POST['oper'];

$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$id_user = $_SESSION['id_user'];

$response['status'] = 'error';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    if ($operacion == 'add') {
        $sql = "INSERT INTO `nom_cargo_empleado`
                (`codigo`,`descripcion_carg`,`grado`,`perfil_siho`,`id_nombramiento`,`id_user_reg`,`fec_reg`)
            VALUES(?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_codigo, PDO::PARAM_INT);
        $sql->bindParam(2, $cargo, PDO::PARAM_STR);
        $sql->bindParam(3, $grado, PDO::PARAM_INT);
        $sql->bindParam(4, $perfil, PDO::PARAM_STR);
        $sql->bindParam(5, $id_nombramiento, PDO::PARAM_INT);
        $sql->bindParam(6, $id_user, PDO::PARAM_INT);
        $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $response['status'] = 'ok';
        } else {
            $response['msg'] =  $sql->errorInfo()[2];
            exit();
        }
    } else if ($operacion == 'edit') {
        $sql = "UPDATE `nom_cargo_empleado`
                    SET `codigo` = ?, `descripcion_carg` = ?, `grado` = ?, `perfil_siho` = ?, `id_nombramiento` = ?
                WHERE (`id_cargo` = ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_codigo, PDO::PARAM_INT);
        $sql->bindParam(2, $cargo, PDO::PARAM_STR);
        $sql->bindParam(3, $grado, PDO::PARAM_INT);
        $sql->bindParam(4, $perfil, PDO::PARAM_STR);
        $sql->bindParam(5, $id_nombramiento, PDO::PARAM_INT);
        $sql->bindParam(6, $id_cargo, PDO::PARAM_INT);
        if (!($sql->execute())) {
            $response['msg'] =  $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $sql = $sql = "UPDATE `nom_cargo_empleado` SET `id_user_act` = ?, `fec_act` = ? WHERE `id_cargo` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id_user, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $id_cargo, PDO::PARAM_INT);
                $sql->execute();
                $response['status'] = 'ok';
            } else {
                $response['msg'] =  'No se realizó ningún cambio';
            }
        }
    } else if ($operacion == 'del') {
        $sql = "DELETE FROM `nom_cargo_empleado` WHERE (`id_cargo` = ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_cargo, PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            include '../../../financiero/reg_logs.php';
            $ruta = '../../../log';
            $consulta = "DELETE FROM `nom_cargo_empleado` WHERE (`id_cargo` = $id_cargo)";
            RegistraLogs($ruta, $consulta);
            $response['status'] = 'ok';
        } else {
            $response['msg'] =  'No se realizó ningún cambio' . $sql->errorInfo()[2];
        }
    } else {
        $response['msg'] =  'Acción no permitida';
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] =  $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

echo json_encode($response);
