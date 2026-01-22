<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$nombrePresupuesto = $_POST['nomPto'];
$id_empresa = '2';
$id_sede = '1';
$tipoPto = $_POST['tipoPto'];
$vigencia = $_SESSION['vigencia'];
$objeto = mb_strtoupper($_POST['txtObjeto']);
$estado = '1';
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_vigencia` FROM `tb_vigencias` WHERE (`anio` = '$vigencia')";
    $res = $cmd->query($sql);
    $datas = $res->fetch();
    $vig = $datas['id_vigencia'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO pto_presupuestos (id_tipo, id_vigencia, nombre, descripcion, id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $tipoPto, PDO::PARAM_INT);
    $sql->bindParam(2, $vig, PDO::PARAM_INT);
    $sql->bindParam(3, $nombrePresupuesto, PDO::PARAM_STR);
    $sql->bindParam(4, $objeto, PDO::PARAM_STR);
    $sql->bindParam(5, $iduser, PDO::PARAM_INT);
    $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo '1';
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
