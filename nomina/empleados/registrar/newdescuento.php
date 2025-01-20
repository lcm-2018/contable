<?php

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$idemple = isset($_POST['idEmpl']) ? $_POST['idEmpl'] : exit('Acción no permitida');
$fecha = $_POST['datFecDcto'];
$fecha2 = $_POST['datFecFinDcto'] == '' ? null : $_POST['datFecFinDcto'];
$tipo = $_POST['sclTipoDcto'];
$concepto = $_POST['txtConDcto'];
$valor = $_POST['numValDcto'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$estado = 1;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `nom_otros_descuentos`
                (`id_empleado`,`id_tipo_dcto`,`fecha`,`fecha_fin`, `concepto`,`valor`,`estado`,`id_user_reg`,`fec_reg`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idemple, PDO::PARAM_INT);
    $sql->bindParam(2, $tipo, PDO::PARAM_INT);
    $sql->bindParam(3, $fecha, PDO::PARAM_STR);
    $sql->bindParam(4, $fecha2, PDO::PARAM_STR);
    $sql->bindParam(5, $concepto, PDO::PARAM_STR);
    $sql->bindParam(6, $valor, PDO::PARAM_STR);
    $sql->bindParam(7, $estado, PDO::PARAM_INT);
    $sql->bindParam(8, $iduser, PDO::PARAM_INT);
    $sql->bindValue(9, $date->format('Y-m-d H:i:s'), PDO::PARAM_STR);
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo 'ok';
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
