<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}
include '../../conexion.php';

$idus = isset($_POST['idUpUser']) ? $_POST['idUpUser'] : exit('Acción no permitida');
$cc = $_POST['txtccUpUser'];
$nomb1 = $_POST['txtNomb1Upuser'];
$nomb2 = $_POST['txtNomb2Upuser'];
$ape1 = $_POST['txtApe1Upuser'];
$ape2 = $_POST['txtApe2Upuser'];
$login = $_POST['txtUplogin'];
$mail = $_POST['mailUpuser'];
$pass = $_POST['passUp'];
$roluser = $_POST['slcRolUpUser'];
$rolant = $_POST['rol_anterior'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_usuarios_sistema` SET `num_documento` = ?, `nombre1`= ?, `nombre2` = ?, `apellido1` = ?, `apellido2` = ?, `login` = ?, `email` = ?, `clave` = ?, `id_rol` = ? WHERE `id_usuario` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $cc, PDO::PARAM_STR);
    $sql->bindParam(2, $nomb1, PDO::PARAM_STR);
    $sql->bindParam(3, $nomb2, PDO::PARAM_STR);
    $sql->bindParam(4, $ape1, PDO::PARAM_STR);
    $sql->bindParam(5, $ape2, PDO::PARAM_STR);
    $sql->bindParam(6, $login, PDO::PARAM_STR);
    $sql->bindParam(7, $mail, PDO::PARAM_STR);
    $sql->bindParam(8, $pass, PDO::PARAM_STR);
    $sql->bindParam(9, $roluser, PDO::PARAM_INT);
    $sql->bindParam(10, $idus, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            if ($roluser != $rolant) {
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "DELETE FROM `seg_rol_usuario`  WHERE `id_usuario` = ?";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $idus, PDO::PARAM_INT);
                    $sql->execute();
                    $sql = "DELETE FROM `seg_permisos_modulos`  WHERE `id_usuario` = ?";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $idus, PDO::PARAM_INT);
                    $sql->execute();
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "SELECT
                            `seg_rol_permisos`.`id_rol`
                            , `seg_rol_permisos`.`id_opcion`
                            , `seg_rol_permisos`.`per_consultar`
                            , `seg_rol_permisos`.`per_adicionar`
                            , `seg_rol_permisos`.`per_modificar`
                            , `seg_rol_permisos`.`per_eliminar`
                            , `seg_rol_permisos`.`per_anular`
                            , `seg_rol_permisos`.`per_imprimir`
                            , `seg_opciones`.`id_modulo`
                        FROM
                            `seg_rol_permisos`
                        INNER JOIN `seg_opciones` 
                            ON (`seg_rol_permisos`.`id_opcion` = `seg_opciones`.`id_opcion`)
                        WHERE (`id_rol` = $roluser)";
                    $opciones = $cmd->query($sql);
                    $opc_reg = 0;
                    $modulos = [];
                    $vacio = [1, 2];

                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $query = "INSERT INTO `seg_rol_usuario` (`id_usuario`, `id_opcion`, `per_consultar`, `per_adicionar`, `per_modificar`, `per_eliminar`, `per_anular`, `per_imprimir`) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $query = $cmd->prepare($query);
                    $query->bindParam(1, $idus, PDO::PARAM_INT);
                    $query->bindParam(2, $id_opcion, PDO::PARAM_INT);
                    $query->bindParam(3, $per_consultar, PDO::PARAM_INT);
                    $query->bindParam(4, $per_adicionar, PDO::PARAM_INT);
                    $query->bindParam(5, $per_modificar, PDO::PARAM_INT);
                    $query->bindParam(6, $per_eliminar, PDO::PARAM_INT);
                    $query->bindParam(7, $per_anular, PDO::PARAM_INT);
                    $query->bindParam(8, $per_imprimir, PDO::PARAM_INT);
                    foreach ($opciones as $op) {
                        $modulos[$op['id_modulo']] = $vacio;
                        $id_opcion = $op['id_opcion'];
                        $per_consultar = $op['per_consultar'];
                        $per_adicionar = $op['per_adicionar'];
                        $per_modificar = $op['per_modificar'];
                        $per_eliminar = $op['per_eliminar'];
                        $per_anular = $op['per_anular'];
                        $per_imprimir = $op['per_imprimir'];
                        $query->execute();
                        if (!($cmd->lastInsertId()) > 0) {
                            echo $cmd->errorInfo()[2];
                        }
                    }
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $squery = "INSERT INTO `seg_permisos_modulos` (`id_usuario`, `id_modulo`) VALUES (?, ?)";
                    $squery = $cmd->prepare($squery);
                    $squery->bindParam(1, $idus, PDO::PARAM_INT);
                    $squery->bindParam(2, $id_modulo, PDO::PARAM_INT);
                    foreach ($modulos as $key => $value) {
                        $id_modulo = $key;
                        $squery->execute();
                        if (!($cmd->lastInsertId()) > 0) {
                            echo $cmd->errorInfo()[2];
                        }
                    }
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
            }
            echo '1';
        } else {
            echo 'No se registró ningún nuevo dato';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
