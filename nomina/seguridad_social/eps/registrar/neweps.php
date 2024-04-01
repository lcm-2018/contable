<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';

function calcularDV($nit)
{
    if (!is_numeric($nit)) {
        return false;
    }

    $arr = array(
        1 => 3, 4 => 17, 7 => 29, 10 => 43, 13 => 59, 2 => 7, 5 => 19,
        8 => 37, 11 => 47, 14 => 67, 3 => 13, 6 => 23, 9 => 41, 12 => 53, 15 => 71
    );
    $x = 0;
    $y = 0;
    $z = strlen($nit);
    $dv = '';

    for ($i = 0; $i < $z; $i++) {
        $y = substr($nit, $i, 1);
        $x += ($y * $arr[$z - $i]);
    }

    $y = $x % 11;

    if ($y > 1) {
        $dv = 11 - $y;
        return $dv;
    } else {
        $dv = $y;
        return $dv;
    }
}

$n = isset($_POST['txtNitEps']) ? $_POST['txtNitEps'] : exit('Acción no permitida');
$nom = mb_strtoupper($_POST['txtNomEps']);
$tel = $_POST['txtTelEps'];
$cor = $_POST['maileps'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT * FROM nom_epss WHERE nit = '$n'";
    $r = $cmd->query($sql);
    if ($r->rowCount() > 0) {
        echo '0';
    } else {
        $dverf = calcularDV($n);
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO nom_epss (nombre_eps, nit, digito_verific, telefono, correo, fec_reg) VALUES (?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $nom, PDO::PARAM_STR);
        $sql->bindParam(2, $n, PDO::PARAM_STR);
        $sql->bindParam(3, $dverf, PDO::PARAM_INT);
        $sql->bindParam(4, $tel, PDO::PARAM_STR);
        $sql->bindParam(5, $cor, PDO::PARAM_STR);
        $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            echo '1';
        } else {
            print_r($sql->errorInfo()[2]);
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
