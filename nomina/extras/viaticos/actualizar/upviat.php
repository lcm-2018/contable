<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$s = "0";
$idviatnew = isset($_POST['idViatUpNew']) ? $_POST['idViatUpNew'] : exit('Acción no permitida');
$idviat = $_POST['idViatUp'];
$concep = $_POST['txtUpConcepto'];
$val = $_POST['numUpValor'];
$fviat = date('Y-m-d', strtotime($_POST['datFecViarUp']));
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE seg_detalle_viaticos SET concepto = ?, valor = ?, fviatico = ? WHERE id_detviatic = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $concep, PDO::PARAM_STR);
    $sql->bindParam(2, $val, PDO::PARAM_INT);
    $sql->bindParam(3, $fviat, PDO::PARAM_STR);
    $sql->bindParam(4, $idviat, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $r = 1;
    } else {
        $r = 0;
    }
    if (!($sql->execute())) {
        print_r($sql->errorInfo()[2]);
        exit();
    }
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO seg_detalle_viaticos (id_viaticos, concepto, valor, fviatico, fec_reg) VALUES (?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_viaticosup, PDO::PARAM_INT);
    $sql->bindParam(2, $conceptnew, PDO::PARAM_STR);
    $sql->bindParam(3, $valornew, PDO::PARAM_INT);
    $sql->bindParam(4, $fviaticonew, PDO::PARAM_STR);
    $sql->bindValue(5, $date->format('Y-m-d H:i:s'));

    for ($i = 1; $i < 5; $i++) {
        if (isset($_POST['numUpValor' . $i])) {
            $id_viaticosup = $idviatnew;
            $conceptnew = $_POST['txtUpConcepto' . $i];
            $valornew = $_POST['numUpValor' . $i];
            $fviaticonew = date('Y-m-d', strtotime($_POST['datFecViarUp' . $i]));
            if ($valornew !== "") {
                $sql->execute();
                $s = $sql->rowCount();
            }
        }
    }
    if ($r > 0) {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $sql = "UPDATE seg_detalle_viaticos SET fec_act = ? WHERE id_detviatic = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(2, $idviat);
        $sql->execute();
    }
    if ($r > 0 || $s > 0) {
        echo '1';
    } else {
        echo 'No se registró ningún nuevo dato.';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
