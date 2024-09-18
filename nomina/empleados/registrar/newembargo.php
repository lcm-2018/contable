<?php

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$idemple = isset($_POST['idEmpEmbargo']) ? $_POST['idEmpEmbargo'] : exit('Acción no permitida');
$juzgado = $_POST['slcJuzgado'];
$idtipemb = $_POST['numTipoEmbargo'];
$valtot = str_replace(',', '', $_POST['numTotEmbargo']);
$dctomax = $_POST['numDctoAprox'];
$dcto = $_POST['txtValEmbargoMes'];
$porcien = $_POST['txtPorcEmbMes'] / 100;
$finemb = date('Y-m-d', strtotime($_POST['datFecInicioEmb']));
if ($_POST['datFecFinEmb'] === '') {
    $ffinemb;
} else {
    $ffinemb = date('Y-m-d', strtotime($_POST['datFecFinEmb']));
}
$estado = $_POST['txtEstadoEmbargo'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO nom_embargos (id_juzgado, id_empleado,tipo_embargo, valor_total, dcto_max, valor_mes, porcentaje, fec_inicio, fec_fin, estado, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $juzgado, PDO::PARAM_INT);
    $sql->bindParam(2, $idemple, PDO::PARAM_INT);
    $sql->bindParam(3, $idtipemb, PDO::PARAM_INT);
    $sql->bindParam(4, $valtot, PDO::PARAM_STR);
    $sql->bindParam(5, $dctomax, PDO::PARAM_STR);
    $sql->bindParam(6, $dcto, PDO::PARAM_STR);
    $sql->bindParam(7, $porcien, PDO::PARAM_STR);
    $sql->bindParam(8, $finemb, PDO::PARAM_STR);
    $sql->bindParam(9, $ffinemb, PDO::PARAM_STR);
    $sql->bindParam(10, $estado, PDO::PARAM_STR);
    $sql->bindValue(11, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo '1';
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
