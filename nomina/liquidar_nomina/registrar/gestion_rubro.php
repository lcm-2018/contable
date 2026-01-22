<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$id_relacion = isset($_POST['id_relacion']) ? $_POST['id_relacion'] : exit("Acción no permitida");
$id_tipo = $_POST['slcTipo'];
$id_vigencia = $_SESSION['id_vigencia'];
$r_admin = $_POST['idRubroAdmin'];
$r_operativo = $_POST['idRubroOpera'];

$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$id_user = $_SESSION['id_user'];
if ($id_relacion == '0') {
    $condicion = '';
}else{
    $condicion = ' AND `id_relacion` <> '.$id_relacion;
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_tipo`,`id_vigencia`
            FROM `nom_rel_rubro`
            WHERE `id_tipo` = $id_tipo AND `id_vigencia` = $id_vigencia $condicion";
    $rs = $cmd->query($sql);
    $valida = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($valida)) {
    echo 'Ya se asignó un rubro a esta relación para la vigencia actual';
    exit();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    if ($id_relacion == '0') {
        $sql = "INSERT INTO `nom_rel_rubro`
                    (`id_tipo`,`r_admin`,`r_operativo`,`id_vigencia`,`id_user_reg`,`fec_reg`)
                VALUES (?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_tipo, PDO::PARAM_INT);
        $sql->bindParam(2, $r_admin, PDO::PARAM_INT);
        $sql->bindParam(3, $r_operativo, PDO::PARAM_INT);
        $sql->bindParam(4, $id_vigencia, PDO::PARAM_INT);
        $sql->bindParam(5, $id_user, PDO::PARAM_INT);
        $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            echo 'ok';
        } else {
            echo $sql->errorInfo()[2];
            exit();
        }
    } else {
        $sql = "UPDATE `nom_rel_rubro`
                    SET `id_tipo` = ? , `r_admin` = ?, `r_operativo` = ?
                WHERE `id_relacion` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_tipo, PDO::PARAM_INT);
        $sql->bindParam(2, $r_admin, PDO::PARAM_INT);
        $sql->bindParam(3, $r_operativo, PDO::PARAM_INT);
        $sql->bindParam(4, $id_relacion, PDO::PARAM_INT);
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $sql = $sql = "UPDATE `nom_rel_rubro` SET `id_user_act` = ?, `fec_act` = ? WHERE `id_relacion` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id_user, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $id_relacion, PDO::PARAM_INT);
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
