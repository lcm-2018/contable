<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$nombrePresupuesto = $_POST['nomPto'];
$tipoPto = $_POST['tipoPto'];
$id = $_POST['id'];
$objeto = mb_strtoupper($_POST['txtObjeto']);
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE pto_presupuestos SET  id_pto_tipo= ?,  nombre= ?, descripcion= ?, id_usuer_act= ?, fec_act= ? WHERE id_pto_presupuestos= ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $tipoPto, PDO::PARAM_INT);
    $sql->bindParam(2, $nombrePresupuesto, PDO::PARAM_STR);
    $sql->bindParam(3, $objeto, PDO::PARAM_STR);
    $sql->bindParam(4, $iduser, PDO::PARAM_INT);
    $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(6, $id, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        echo '1';
    } else {
        print_r($sql->errorInfo()[2]);
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
