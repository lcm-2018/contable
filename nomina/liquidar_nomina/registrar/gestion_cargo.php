<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$id_cargo = isset($_POST['id_cargo']) ? $_POST['id_cargo'] : exit('Acceso denegado');
$id_codigo = $_POST['slcCodigo'];
$cargo = $_POST['txtNomCargo'];
$grado = $_POST['numGrado'];
$perfil = $_POST['txtPerfilSiho'];
$id_nombramiento = $_POST['slcNombramiento'];

$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$id_user = $_SESSION['id_user'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    if ($id_cargo == '0') {
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
            echo 'ok';
        } else {
            echo $sql->errorInfo()[2];
            exit();
        }
    } else {
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
            echo $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $sql = $sql = "UPDATE `nom_cargo_empleado` SET `id_user_act` = ?, `fec_act` = ? WHERE `id_cargo` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id_user, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $id_cargo, PDO::PARAM_INT);
                $sql->execute();
                echo 'ok';
            } else {
                echo 'No se realizó ningún cambio';
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
