<?php

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id = isset($_POST['id_cc']) ? $_POST['id_cc'] : exit('Acción no permitida');
$id_cc = $_POST['slcCcostoEmpl'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `nom_ccosto_empleado` SET `id_ccosto` = ? WHERE `id_cc_emp` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_cc, PDO::PARAM_INT);
    $sql->bindParam(2, $id, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
    } else {
        if ($sql->rowCount() > 0) {
            $sql = "UPDATE `nom_ccosto_empleado` SET `fec_act` = ?, `id_user_act` = ? WHERE `id_cc_emp` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(2, $id_user, PDO::PARAM_INT);
            $sql->bindParam(3, $id, PDO::PARAM_INT);
            $sql->execute();
            echo 'ok';
        } else {
            echo 'No se realizó ningún cambio';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
